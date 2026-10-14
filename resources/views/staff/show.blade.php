@extends('layouts.app')

@section('title', 'Staff Profile: ' . $user->name)
@section('page_title', 'Staff & Specialist Profile')

@section('content')
<div class="row g-4">
    <!-- Left: Profile Summary -->
    <div class="col-12 col-lg-4">
        <div class="card aura-card text-center p-4 mb-4">
            <div class="mx-auto rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center shadow mb-3" style="width: 72px; height: 72px; font-size: 1.8rem;">
                {{ substr($user->name, 0, 1) }}
            </div>
            <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
            <div class="text-muted small mb-2">{{ $user->specialization ?? 'Spa & Salon Professional' }}</div>

            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge bg-primary text-uppercase px-3 py-1">{{ $user->role }}</span>
                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }} px-3 py-1">{{ ucfirst($user->status) }}</span>
            </div>

            <div class="border-top border-bottom py-3 my-2 row g-2">
                <div class="col-6 border-end">
                    <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Lifetime Commissions</div>
                    <div class="fw-bold fs-5 text-success">${{ number_format($totalEarned, 2) }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small text-uppercase" style="font-size: 0.7rem;">Pending Payout</div>
                    <div class="fw-bold fs-5 text-warning">${{ number_format($pendingCommission, 2) }}</div>
                </div>
            </div>

            <div class="text-start mt-3 small text-muted">
                <div class="mb-2"><i class="bi bi-envelope text-secondary me-2"></i><strong>Email:</strong> {{ $user->email }}</div>
                <div class="mb-2"><i class="bi bi-telephone text-secondary me-2"></i><strong>Phone:</strong> {{ $user->phone ?? 'None' }}</div>
                <div class="mb-2"><i class="bi bi-percent text-secondary me-2"></i><strong>Commission Rate:</strong> {{ $user->commission_rate }}%</div>
            </div>

            @if($user->bio)
                <div class="text-start mt-3 pt-3 border-top small text-muted">
                    <strong>Professional Bio:</strong>
                    <p class="mb-0 mt-1 fst-italic">{{ $user->bio }}</p>
                </div>
            @endif

            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('staff.edit', $user) }}" class="btn btn-aura-primary">
                    <i class="bi bi-pencil me-1"></i>Edit Profile
                </a>
            </div>
        </div>

        <!-- Weekly Shift Schedule -->
        <div class="card aura-card p-3 mb-4">
            <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-clock-history me-1"></i>Weekly Working Hours</h6>
            <div class="list-group list-group-flush small">
                @forelse($user->schedules->sortBy('day_of_week') as $sch)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="fw-semibold">{{ $sch->day_name }}</span>
                        @if($sch->is_working)
                            <span class="badge bg-light text-dark border">
                                {{ date('g:i A', strtotime($sch->start_time)) }} - {{ date('g:i A', strtotime($sch->end_time)) }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Day Off</span>
                        @endif
                    </div>
                @empty
                    <div class="text-muted text-center py-3">Standard 9:00 AM - 6:00 PM (Mon-Sat)</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right: Appointments & Commissions -->
    <div class="col-12 col-lg-8">
        <!-- Assigned Appointments -->
        <div class="card aura-card mb-4">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar2-check text-primary me-2"></i>Client Appointments Handled</h6>
                <span class="badge bg-light text-dark border">Recent</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Booking #</th>
                                <th>Date & Time</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->therapistAppointments as $apt)
                                <tr>
                                    <td>
                                        <a href="{{ route('appointments.show', $apt) }}" class="fw-bold text-dark text-decoration-none small">
                                            {{ $apt->appointment_number }}
                                        </a>
                                    </td>
                                    <td class="small">
                                        {{ $apt->appointment_date->format('M d, Y') }} ({{ date('h:i A', strtotime($apt->start_time)) }})
                                    </td>
                                    <td class="small fw-semibold">{{ $apt->customer->name }}</td>
                                    <td>
                                        @foreach($apt->items as $item)
                                            <span class="badge bg-light text-dark border small me-1">{{ $item->service->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge {{ $apt->status_badge_class }} text-uppercase" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $apt->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No client bookings recorded for this specialist.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Commission Payouts History -->
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack text-warning me-2"></i>Recent Commission Ledger</h6>
                <a href="{{ route('commissions.index', ['therapist_id' => $user->id]) }}" class="small text-decoration-none">Full Ledger &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Date</th>
                                <th>Item / Service</th>
                                <th>Status</th>
                                <th class="text-end">Commission Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->commissionPayouts as $payout)
                                <tr>
                                    <td class="small text-muted">{{ $payout->created_at->format('M d, Y') }}</td>
                                    <td class="small">{{ $payout->invoiceItem?->item_name ?? 'POS Service Sale' }}</td>
                                    <td>
                                        <span class="badge {{ $payout->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase" style="font-size: 0.65rem;">
                                            {{ $payout->status }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success small">${{ number_format($payout->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No commission records yet.</td>
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
