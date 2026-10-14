<?php

namespace App\Http\Controllers;

use App\Models\StaffSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('specialization', 'ilike', "%{$search}%");
            });
        }

        $staff = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:admin,manager,receptionist,therapist'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive,on_leave'],
            'bio' => ['nullable', 'string'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Seed default schedules for therapists
        if ($user->role === 'therapist') {
            for ($day = 1; $day <= 6; $day++) {
                StaffSchedule::create([
                    'user_id' => $user->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'is_working' => true,
                ]);
            }
        }

        return redirect()->route('staff.index')->with('success', "Staff member {$user->name} added.");
    }

    public function show(User $user)
    {
        $user->load([
            'schedules',
            'therapistAppointments' => fn($q) => $q->with(['customer', 'items.service'])->orderBy('appointment_date', 'desc')->take(10),
            'commissionPayouts' => fn($q) => $q->orderBy('created_at', 'desc')->take(15),
        ]);

        $totalEarned = $user->commissionPayouts()->sum('amount');
        $pendingCommission = $user->commissionPayouts()->where('status', 'pending')->sum('amount');

        return view('staff.show', compact('user', 'totalEarned', 'pendingCommission'));
    }

    public function edit(User $user)
    {
        return view('staff.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:admin,manager,receptionist,therapist'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive,on_leave'],
            'bio' => ['nullable', 'string'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('staff.show', $user)->with('success', "Staff member {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('staff.index')->with('success', 'Staff member deleted.');
    }
}
