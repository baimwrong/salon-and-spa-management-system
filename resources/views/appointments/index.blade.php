@extends('layouts.app')

@section('title', 'Appointments & Scheduling')
@section('page_title', 'Appointments Calendar & Schedule')

@section('content')
<div class="card aura-card mb-4">
    <div class="aura-card-header pb-3">
        <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Appointment Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
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
                <label class="form-label small fw-semibold text-muted mb-1">Therapist</label>
                <select name="therapist_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Staff</option>
                    @foreach($therapists as $t)
                        <option value="{{ $t->id }}" {{ request('therapist_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4 text-md-end pt-md-4 d-flex justify-content-end gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" class="btn btn-outline-secondary {{ $viewMode === 'table' ? 'active' : '' }}" title="Table View">
                        <i class="bi bi-list-ul"></i> Table
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'timeline']) }}" class="btn btn-outline-secondary {{ $viewMode === 'timeline' ? 'active' : '' }}" title="Visual Timeline Grid">
                        <i class="bi bi-calendar3-range"></i> Timeline
                    </a>
                </div>
                <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-aura-gold">
                    <i class="bi bi-calendar-plus me-1"></i>Book Appointment
                </a>
            </div>
        </form>
    </div>

    @if($viewMode === 'timeline')
        <!-- Visual Specialist Schedule Timeline Grid -->
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small fw-bold text-uppercase text-muted">Specialist Day Board for {{ date('F j, Y', strtotime($selectedDate)) }}</span>
                <span class="badge bg-light text-dark border">{{ $appointments->count() }} Booking(s)</span>
            </div>

            <div class="row g-3">
                @foreach($therapists as $t)
                    @php
                        $tAppts = $appointments->where('therapist_id', $t->id);
                    @endphp
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border rounded-3 h-100 bg-light bg-opacity-25 shadow-sm">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold small text-dark">{{ $t->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $t->specialization }}</div>
                                </div>
                                <span class="badge bg-secondary rounded-pill" style="font-size: 0.65rem;">{{ $tAppts->count() }}</span>
                            </div>

                            <div class="card-body p-2" style="min-height: 250px;">
                                @forelse($tAppts as $apt)
                                    <div class="card p-2 mb-2 border-start border-3 border-{{ $apt->status === 'completed' ? 'success' : ($apt->status === 'in_service' ? 'primary' : 'warning') }} shadow-sm bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge {{ $apt->status_badge_class }}" style="font-size: 0.65rem;">{{ strtoupper($apt->status) }}</span>
                                            <span class="small text-muted fw-bold">{{ date('h:i A', strtotime($apt->start_time)) }}</span>
                                        </div>
                                        <div class="fw-bold small text-dark mb-1">{{ $apt->customer->name }}</div>
                                        <div class="text-muted small text-truncate mb-2" style="font-size: 0.72rem;">
                                            @foreach($apt->items as $i)
                                                <span>{{ $i->service->name }}</span>@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="font-size: 0.7rem;">
                                            <span class="text-muted"><i class="bi bi-door-open me-1"></i>{{ $apt->room?->name ?? 'Station' }}</span>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('appointments.show', $apt) }}" class="btn btn-sm btn-link p-0 text-dark" title="View"><i class="bi bi-eye"></i></a>
                                                @if(!in_array($apt->status, ['completed', 'cancelled']))
                                                    <a href="{{ route('pos.terminal', ['appointment_id' => $apt->id]) }}" class="btn btn-sm btn-link p-0 text-success fw-bold ms-1" title="POS Checkout"><i class="bi bi-cash-coin"></i></a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small">
                                        <i class="bi bi-clock-history d-block text-secondary mb-1"></i>
                                        No bookings scheduled
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Standard Table View -->
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
    @endif
</div>
@endsection
