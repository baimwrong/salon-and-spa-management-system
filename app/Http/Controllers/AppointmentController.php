<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\SpaRoom;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $viewMode = $request->input('view', 'table'); // table or timeline

        $query = Appointment::with(['customer', 'therapist', 'room', 'items.service', 'items.therapist']);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $selectedDate);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('therapist_id')) {
            $query->where('therapist_id', $request->input('therapist_id'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $therapists = User::where('role', 'therapist')->where('status', 'active')->orderBy('name')->get();
        $rooms = SpaRoom::where('is_active', true)->orderBy('name')->get();

        if ($viewMode === 'timeline') {
            $appointments = $query->orderBy('start_time', 'asc')->get();
            return view('appointments.index', compact('appointments', 'therapists', 'rooms', 'selectedDate', 'viewMode'));
        }

        $appointments = $query->orderBy('start_time', 'asc')->paginate(20)->withQueryString();

        return view('appointments.index', compact('appointments', 'therapists', 'rooms', 'selectedDate', 'viewMode'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', '!=', 'blocked')->orderBy('name')->get();
        $therapists = User::where('role', 'therapist')->where('status', 'active')->orderBy('name')->get();
        $rooms = SpaRoom::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->with('category')->orderBy('name')->get();

        $preselectedCustomerId = $request->query('customer_id');

        return view('appointments.create', compact('customers', 'therapists', 'rooms', 'services', 'preselectedCustomerId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'therapist_id' => ['nullable', 'exists:users,id'],
            'room_id' => ['nullable', 'exists:spa_rooms,id'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['required', 'string', 'in:walk_in,phone,online'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['exists:services,id'],
            'item_therapist_ids' => ['nullable', 'array'],
            'item_therapist_ids.*' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();

        // Enforce room requirement validation
        $requiresRoom = $services->contains('requires_room', true);
        if ($requiresRoom && empty($validated['room_id'])) {
            return back()->withInput()->with('error', 'One or more of the selected treatments require a dedicated spa room/suite. Please select a room.');
        }

        $totalDurationMinutes = $services->sum(fn($s) => $s->duration_minutes + $s->buffer_time_minutes);
        $totalPrice = $services->sum('price');

        $startCarbon = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date'] . ' ' . $validated['start_time']);
        $endCarbon = (clone $startCarbon)->addMinutes($totalDurationMinutes);

        try {
            $this->validateScheduleAndConflicts(
                $validated['therapist_id'] ?? null,
                $validated['room_id'] ?? null,
                $validated['appointment_date'],
                $startCarbon,
                $endCarbon
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        DB::beginTransaction();
        try {
            // Sequential & Compliant Appointment Number
            $datePrefix = str_replace('-', '', $validated['appointment_date']);
            $prefix = "APT-{$datePrefix}-";
            $lastApt = Appointment::where('appointment_number', 'LIKE', "{$prefix}%")
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($lastApt && preg_match('/-(\d+)$/', $lastApt->appointment_number, $matches)) {
                $nextSeq = intval($matches[1]) + 1;
            } else {
                $nextSeq = 1;
            }
            $appointmentNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $appointment = Appointment::create([
                'appointment_number' => $appointmentNumber,
                'customer_id' => $validated['customer_id'],
                'therapist_id' => $validated['therapist_id'] ?? null,
                'room_id' => $validated['room_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $startCarbon->format('H:i:s'),
                'end_time' => $endCarbon->format('H:i:s'),
                'status' => 'confirmed',
                'booking_type' => $validated['booking_type'],
                'subtotal' => $totalPrice,
                'discount_amount' => 0.00,
                'tax_amount' => round($totalPrice * 0.10, 2),
                'total_amount' => round($totalPrice * 1.10, 2),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($services as $service) {
                $itemTherapistId = $validated['item_therapist_ids'][$service->id] ?? $validated['therapist_id'] ?? null;
                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $service->id,
                    'therapist_id' => $itemTherapistId,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                ]);
            }

            DB::commit();

            return redirect()->route('appointments.show', $appointment)
                ->with('success', "Appointment {$appointment->appointment_number} scheduled successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to schedule appointment: ' . $e->getMessage());
        }
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'therapist', 'room', 'items.service', 'items.therapist', 'invoice']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'Completed appointments cannot be rescheduled.');
        }

        $appointment->load(['customer', 'therapist', 'room', 'items']);
        $customers = Customer::where('status', '!=', 'blocked')->orderBy('name')->get();
        $therapists = User::where('role', 'therapist')->where('status', 'active')->orderBy('name')->get();
        $rooms = SpaRoom::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->with('category')->orderBy('name')->get();
        $selectedServiceIds = $appointment->items->pluck('service_id')->toArray();
        $itemTherapists = $appointment->items->pluck('therapist_id', 'service_id')->toArray();

        return view('appointments.edit', compact('appointment', 'customers', 'therapists', 'rooms', 'services', 'selectedServiceIds', 'itemTherapists'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointments cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'therapist_id' => ['nullable', 'exists:users,id'],
            'room_id' => ['nullable', 'exists:spa_rooms,id'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['required', 'string', 'in:walk_in,phone,online'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['exists:services,id'],
            'item_therapist_ids' => ['nullable', 'array'],
            'item_therapist_ids.*' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();

        $requiresRoom = $services->contains('requires_room', true);
        if ($requiresRoom && empty($validated['room_id'])) {
            return back()->withInput()->with('error', 'One or more of the selected treatments require a dedicated spa room/suite. Please select a room.');
        }

        $totalDurationMinutes = $services->sum(fn($s) => $s->duration_minutes + $s->buffer_time_minutes);
        $totalPrice = $services->sum('price');

        $startCarbon = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date'] . ' ' . $validated['start_time']);
        $endCarbon = (clone $startCarbon)->addMinutes($totalDurationMinutes);

        try {
            $this->validateScheduleAndConflicts(
                $validated['therapist_id'] ?? null,
                $validated['room_id'] ?? null,
                $validated['appointment_date'],
                $startCarbon,
                $endCarbon,
                $appointment->id
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $appointment->update([
                'customer_id' => $validated['customer_id'],
                'therapist_id' => $validated['therapist_id'] ?? null,
                'room_id' => $validated['room_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $startCarbon->format('H:i:s'),
                'end_time' => $endCarbon->format('H:i:s'),
                'booking_type' => $validated['booking_type'],
                'subtotal' => $totalPrice,
                'tax_amount' => round($totalPrice * 0.10, 2),
                'total_amount' => round($totalPrice * 1.10, 2),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Recreate appointment items
            $appointment->items()->delete();
            foreach ($services as $service) {
                $itemTherapistId = $validated['item_therapist_ids'][$service->id] ?? $validated['therapist_id'] ?? null;
                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $service->id,
                    'therapist_id' => $itemTherapistId,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                ]);
            }

            DB::commit();

            return redirect()->route('appointments.show', $appointment)
                ->with('success', "Appointment {$appointment->appointment_number} updated and rescheduled successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update appointment: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,in_service,completed,cancelled,no_show'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $appointment->status;
            $newStatus = $validated['status'];

            $appointment->update([
                'status' => $newStatus,
                'cancellation_reason' => $newStatus === 'cancelled' ? ($validated['cancellation_reason'] ?? 'Not specified') : null,
            ]);

            // Auto-deduct backbar consumables if marked completed and wasn't previously completed
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $appointment->load('items.service.consumables.product');
                foreach ($appointment->items as $item) {
                    if ($item->service && $item->service->consumables->isNotEmpty()) {
                        foreach ($item->service->consumables as $consumable) {
                            $prod = Product::where('id', $consumable->product_id)->lockForUpdate()->first();
                            if ($prod) {
                                $prod->decrement('stock_quantity', $consumable->quantity_used);
                                StockMovement::create([
                                    'product_id' => $prod->id,
                                    'user_id' => auth()->id(),
                                    'type' => 'service_usage',
                                    'quantity' => -$consumable->quantity_used,
                                    'unit_cost' => $prod->cost_price,
                                    'reference_number' => $appointment->appointment_number,
                                    'notes' => "Auto backbar usage for completed appointment {$appointment->appointment_number}",
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', "Appointment status updated to [{$newStatus}].");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function destroy(Appointment $appointment)
    {
        if (!auth()->user()->isManager()) {
            return back()->with('error', 'Only managers and administrators can delete appointments.');
        }

        if ($appointment->invoice) {
            return back()->with('error', 'Cannot delete an appointment that already has a paid or generated invoice.');
        }

        $appointment->delete();
        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    /**
     * Validate therapist working schedule and check for appointment double-bookings.
     */
    protected function validateScheduleAndConflicts(?int $therapistId, ?int $roomId, string $date, Carbon $startCarbon, Carbon $endCarbon, ?int $excludeAppointmentId = null): void
    {
        $startStr = $startCarbon->format('H:i:s');
        $endStr = $endCarbon->format('H:i:s');

        // 1. Enforce Therapist Shift & Working Hours
        if ($therapistId) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $schedule = \App\Models\StaffSchedule::where('user_id', $therapistId)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($schedule) {
                if (!$schedule->is_working) {
                    $therapist = User::find($therapistId);
                    throw new \RuntimeException("Schedule Conflict: Therapist {$therapist->name} is scheduled OFF on {$schedule->day_name}s.");
                }

                if ($startStr < $schedule->start_time || $endStr > $schedule->end_time) {
                    $therapist = User::find($therapistId);
                    $shift = date('h:i A', strtotime($schedule->start_time)) . ' - ' . date('h:i A', strtotime($schedule->end_time));
                    throw new \RuntimeException("Time Window Conflict: Therapist {$therapist->name}'s working shift on {$schedule->day_name} is {$shift}. Requested booking is outside working hours.");
                }
            }

            // 2. Overlapping appointment for therapist
            $therapistConflict = Appointment::where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->when($excludeAppointmentId, fn($q) => $q->where('id', '!=', $excludeAppointmentId))
                ->where('therapist_id', $therapistId)
                ->where(function ($q) use ($startStr, $endStr) {
                    $q->where('start_time', '<', $endStr)
                      ->where('end_time', '>', $startStr);
                })
                ->first();

            if ($therapistConflict) {
                $t = User::find($therapistId);
                $confWindow = date('h:i A', strtotime($therapistConflict->start_time)) . ' - ' . date('h:i A', strtotime($therapistConflict->end_time));
                throw new \RuntimeException("Booking Collision: Therapist {$t->name} already has appointment #{$therapistConflict->appointment_number} scheduled at {$confWindow}.");
            }
        }

        // 3. Overlapping appointment for room/suite
        if ($roomId) {
            $roomConflict = Appointment::where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->when($excludeAppointmentId, fn($q) => $q->where('id', '!=', $excludeAppointmentId))
                ->where('room_id', $roomId)
                ->where(function ($q) use ($startStr, $endStr) {
                    $q->where('start_time', '<', $endStr)
                      ->where('end_time', '>', $startStr);
                })
                ->first();

            if ($roomConflict) {
                $r = SpaRoom::find($roomId);
                $confWindow = date('h:i A', strtotime($roomConflict->start_time)) . ' - ' . date('h:i A', strtotime($roomConflict->end_time));
                throw new \RuntimeException("Room Collision: Suite/Station '{$r->name}' is already booked from {$confWindow} (#{$roomConflict->appointment_number}).");
            }
        }
    }
}
