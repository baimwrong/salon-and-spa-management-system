<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('preferredTherapist');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('customer_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('skin_type')) {
            $query->where('skin_type', $request->input('skin_type'));
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $therapists = User::where('role', 'therapist')->where('status', 'active')->get();

        return view('customers.index', compact('customers', 'therapists'));
    }

    public function create()
    {
        $therapists = User::where('role', 'therapist')->where('status', 'active')->get();
        return view('customers.create', compact('therapists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'string', 'in:female,male,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'skin_type' => ['nullable', 'string', 'max:100'],
            'hair_type' => ['nullable', 'string', 'max:100'],
            'allergies' => ['nullable', 'string'],
            'preferred_therapist_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive,vip,blocked'],
        ]);

        $validated['customer_code'] = 'CUST-' . strtoupper(Str::random(6));

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->name} created successfully.");
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'preferredTherapist',
            'appointments' => fn($q) => $q->with(['therapist', 'room', 'items.service'])->orderBy('appointment_date', 'desc')->take(10),
            'invoices' => fn($q) => $q->orderBy('created_at', 'desc')->take(10),
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $therapists = User::where('role', 'therapist')->where('status', 'active')->get();
        return view('customers.edit', compact('customer', 'therapists'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:customers,phone,' . $customer->id],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'string', 'in:female,male,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'skin_type' => ['nullable', 'string', 'max:100'],
            'hair_type' => ['nullable', 'string', 'max:100'],
            'allergies' => ['nullable', 'string'],
            'preferred_therapist_id' => ['nullable', 'exists:users,id'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive,vip,blocked'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->name} details updated.");
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer record deleted.');
    }
}
