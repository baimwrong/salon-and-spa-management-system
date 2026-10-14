@extends('layouts.app')

@section('title', 'Appointments & Scheduling')
@section('page_title', 'Appointments Calendar & Schedule')

@section('content')
<div class="card aura-card mb-4">
    <div class="aura-card-header">
        <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Appointment Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Filter by Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="in_service" {{ request('status') === 'in_service' ? 'selected' : '' }}>In-Service</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No-Show</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Filter by Therapist</label>
                <select name="therapist_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Staff</option>
                    @foreach($therapists as $t)
                        <option value="{{ $t->id }}" {{ request('therapist_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 text-md-end pt-md-4">
                <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-aura-gold w-100">
                    <i class="bi bi-calendar-plus me-1"></i>Book Appointment
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Booking #</th>
                        <th>Time Window</th>
                        <th>Client</th>
                        <th>Services Requested</th>
                        <th>Stylist / Therapist</th>
                        <th>Suite / Station</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr>
                            <td>
                                <a href="{{ route('appointments.show', $apt) }}" class="fw-bold text-dark text-decoration-none small">
                                    {{ $apt->appointment_number }}
                                </a>
                                <div class="text-muted" style="font-size: 0.72rem;">Type: {{ ucfirst($apt->booking_type) }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold small text-dark">
                                    {{ date('h:i A', strtotime($apt->start_time)) }} - {{ date('h:i A', strtotime($apt->end_time)) }}
                                </div>
                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $apt->appointment_date->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $apt->customer) }}" class="text-decoration-none fw-semibold text-dark small">
                                    {{ $apt->customer->name }}
                                </a>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $apt->customer->phone }}</div>
                            </td>
                            <td>
                                @foreach($apt->items as $item)
                                    <span class="badge bg-light text-dark border me-1 small">
                                        {{ $item->service->name }} (${{ number_format($item->price, 2) }})
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $apt->therapist?->name ?? 'Unassigned' }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $apt->therapist?->specialization ?? '' }}</div>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $apt->room?->name ?? 'Open Station' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $apt->status_badge_class }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $apt->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('appointments.show', $apt) }}" class="btn btn-light border" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($apt->status !== 'completed' && $apt->status !== 'cancelled')
                                        <a href="{{ route('pos.terminal', ['appointment_id' => $apt->id]) }}" class="btn btn-outline-success" title="Checkout at POS">
                                            <i class="bi bi-cash-coin me-1"></i>POS
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                                No appointments found for {{ date('F j, Y', strtotime($selectedDate)) }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($appointments->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $appointments->links() }}
        </div>
    @endif
</div>
@endsection
