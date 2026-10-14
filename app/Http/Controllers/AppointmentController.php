<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\SpaRoom;
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
        $query = Appointment::with(['customer', 'therapist', 'room', 'items.service']);

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

        $appointments = $query->orderBy('start_time', 'asc')->paginate(20)->withQueryString();

        $therapists = User::where('role', 'therapist')->where('status', 'active')->get();
        $rooms = SpaRoom::where('is_active', true)->get();

        return view('appointments.index', compact('appointments', 'therapists', 'rooms', 'selectedDate'));
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
            'notes' => ['nullable', 'string'],
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $totalDurationMinutes = $services->sum(fn($s) => $s->duration_minutes + $s->buffer_time_minutes);
        $totalPrice = $services->sum('price');

        $startCarbon = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date'] . ' ' . $validated['start_time']);
        $endCarbon = (clone $startCarbon)->addMinutes($totalDurationMinutes);

        $datePrefix = str_replace('-', '', $validated['appointment_date']);
        $appointmentNumber = 'APT-' . $datePrefix . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
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
                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $service->id,
                    'therapist_id' => $validated['therapist_id'] ?? null,
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
        $appointment->load(['customer', 'therapist', 'room', 'items.service', 'invoice']);
        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,in_service,completed,cancelled,no_show'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'cancellation_reason' => $validated['status'] === 'cancelled' ? ($validated['cancellation_reason'] ?? 'Not specified') : null,
        ]);

        return back()->with('success', "Appointment status updated to [{$validated['status']}].");
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }
}
