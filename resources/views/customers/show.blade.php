@extends('layouts.app')

@section('title', 'Client Profile: ' . $customer->name)
@section('page_title', 'Client 360° Profile')

@section('content')
<div class="row g-4">
    <!-- Left: Profile Summary Card -->
    <div class="col-12 col-lg-4">
        <div class="card aura-card text-center p-4 mb-4">
            <div class="mx-auto rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center shadow mb-3" style="width: 72px; height: 72px; font-size: 1.8rem;">
                {{ substr($customer->name, 0, 1) }}
            </div>
            <h5 class="fw-bold mb-1 text-dark">{{ $customer->name }}</h5>
            <div class="text-muted small mb-2">{{ $customer->customer_code }}</div>

            <div class="d-flex justify-content-center gap-2 mb-3">
                @if($customer->status === 'vip')
                    <span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-gem me-1"></i>VIP Guest</span>
                @else
                    <span class="badge bg-success px-3 py-1 text-uppercase">{{ $customer->status }}</span>
                @endif
                <span class="badge bg-soft-gold text-dark px-3 py-1"><i class="bi bi-stars text-warning me-1"></i>{{ $customer->loyalty_points }} Points</span>
            </div>

            <div class="border-top border-bottom py-3 my-2 row g-2">
                <div class="col-6 border-end">
                    <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Lifetime Spend</div>
                    <div class="fw-bold fs-5 text-dark">${{ number_format($customer->total_spend, 2) }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Visits Completed</div>
                    <div class="fw-bold fs-5 text-dark">{{ $customer->completed_visits_count }}</div>
                </div>
            </div>

            <div class="text-start mt-3 small text-muted">
                <div class="mb-2"><i class="bi bi-telephone text-secondary me-2"></i><strong>Phone:</strong> {{ $customer->phone }}</div>
                <div class="mb-2"><i class="bi bi-envelope text-secondary me-2"></i><strong>Email:</strong> {{ $customer->email ?? 'Not provided' }}</div>
                <div class="mb-2"><i class="bi bi-geo-alt text-secondary me-2"></i><strong>Address:</strong> {{ $customer->address ?? 'Not provided' }}</div>
                <div class="mb-2"><i class="bi bi-person-heart text-secondary me-2"></i><strong>Preferred Stylist:</strong> {{ $customer->preferredTherapist?->name ?? 'None' }}</div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('appointments.create', ['customer_id' => $customer->id]) }}" class="btn btn-aura-gold fw-bold">
                    <i class="bi bi-calendar-plus me-1"></i>Book New Appointment
                </a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light border btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Profile
                </a>
            </div>
        </div>

        <!-- Allergy & Health Alert -->
        @if($customer->allergies)
            <div class="alert alert-danger shadow-sm d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-shield-exclamation fs-4 text-danger flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold mb-1">Medical & Allergy Alert</h6>
                    <p class="small mb-0">{{ $customer->allergies }}</p>
                </div>
            </div>
        @endif

        <!-- Diagnostic & Beauty Card -->
        <div class="card aura-card p-3 mb-4">
            <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-heart-pulse me-1"></i>Beauty & Skin Diagnostics</h6>
            <div class="row g-2 small">
                <div class="col-6">
                    <div class="text-muted">Skin Type:</div>
                    <div class="fw-semibold text-dark">{{ $customer->skin_type ?? 'Unspecified' }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted">Hair Texture:</div>
                    <div class="fw-semibold text-dark">{{ $customer->hair_type ?? 'Unspecified' }}</div>
                </div>
            </div>

            @if($customer->notes)
                <div class="mt-3 pt-2 border-top small text-muted">
                    <strong>Personal Notes:</strong>
                    <p class="mb-0 mt-1 fst-italic">{{ $customer->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right: Appointments & Invoices History -->
    <div class="col-12 col-lg-8">
        <!-- Recent Appointments -->
        <div class="card aura-card mb-4">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event text-primary me-2"></i>Visit History & Appointments</h6>
                <a href="{{ route('appointments.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i>Book
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Booking #</th>
                                <th>Date & Time</th>
                                <th>Services</th>
                                <th>Therapist</th>
                                <th>Status</th>
                                <th class="text-end">Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->appointments as $apt)
                                <tr>
                                    <td>
                                        <a href="{{ route('appointments.show', $apt) }}" class="fw-bold text-dark text-decoration-none small">
                                            {{ $apt->appointment_number }}
                                        </a>
                                    </td>
                                    <td class="small">
                                        <div>{{ $apt->appointment_date->format('M d, Y') }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ date('h:i A', strtotime($apt->start_time)) }}</div>
                                    </td>
                                    <td>
                                        @foreach($apt->items as $item)
                                            <span class="badge bg-light text-dark border me-1 small">{{ $item->service->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="small">{{ $apt->therapist?->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge {{ $apt->status_badge_class }} text-uppercase" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $apt->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold small text-dark">${{ number_format($apt->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No past appointments recorded for this client.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoices & Billing History -->
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-receipt text-warning me-2"></i>Invoices & Purchases</h6>
                <span class="badge bg-light text-dark border">POS Verified</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->invoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('pos.invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none small">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">{{ $inv->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $inv->payment_method) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $inv->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase" style="font-size: 0.65rem;">
                                            {{ $inv->payment_status }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-dark small">${{ number_format($inv->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('pos.invoices.show', $inv) }}" class="btn btn-sm btn-light border py-0">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No billing transactions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
