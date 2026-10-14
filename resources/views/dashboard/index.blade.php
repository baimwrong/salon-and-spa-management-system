@extends('layouts.app')

@section('title', 'Operations Dashboard')
@section('page_title', 'Operations & Financial Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <!-- Today's Revenue -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Today's Revenue</div>
                <h3 class="fw-bold my-1 text-dark">${{ number_format($todayRevenue, 2) }}</h3>
                <div class="small text-success d-flex align-items-center gap-1">
                    <i class="bi bi-calendar-check"></i>
                    <span>Month: ${{ number_format($monthRevenue, 2) }}</span>
                </div>
            </div>
            <div class="aura-stat-icon aura-icon-gold">
                <i class="bi bi-currency-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Appointments Today -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Today's Bookings</div>
                <h3 class="fw-bold my-1 text-dark">{{ $todayAppointmentsCount }}</h3>
                <div class="small text-muted">
                    <span class="text-primary fw-semibold">{{ $todayInServiceCount }} In-Service</span> &bull;
                    <span class="text-success fw-semibold">{{ $todayCompletedCount }} Done</span>
                </div>
            </div>
            <div class="aura-stat-icon aura-icon-blue">
                <i class="bi bi-calendar-event"></i>
            </div>
        </div>
    </div>

    <!-- Active Customers -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Client Base</div>
                <h3 class="fw-bold my-1 text-dark">{{ $totalCustomers }}</h3>
                <div class="small text-success d-flex align-items-center gap-1">
                    <i class="bi bi-person-plus"></i>
                    <span>+{{ $newCustomersThisMonth }} new this month</span>
                </div>
            </div>
            <div class="aura-stat-icon aura-icon-rose">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Inventory Status</div>
                <h3 class="fw-bold my-1 {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $lowStockCount }}
                </h3>
                <div class="small text-muted">
                    @if($lowStockCount > 0)
                        <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle"></i> Low stock items</span>
                    @else
                        <span class="text-success"><i class="bi bi-check2-circle"></i> Stock levels healthy</span>
                    @endif
                </div>
            </div>
            <div class="aura-stat-icon {{ $lowStockCount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'aura-icon-green' }}">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Performance Row -->
<div class="row g-3 mb-4">
    <!-- Revenue Chart -->
    <div class="col-12 col-lg-8">
        <div class="card aura-card h-100">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-warning me-2"></i>7-Day Revenue Trajectory</h6>
                <span class="badge bg-light text-dark border">POS Real-time</span>
            </div>
            <div class="card-body">
                <canvas id="revenueTrendChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Stylists / Therapists -->
    <div class="col-12 col-lg-4">
        <div class="card aura-card h-100">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-award text-warning me-2"></i>Top Therapists</h6>
                <a href="{{ route('commissions.index') }}" class="small text-decoration-none">Ledger &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($topTherapists as $therapist)
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    {{ substr($therapist->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small text-dark">{{ $therapist->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $therapist->specialization ?? 'Therapist' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success small">${{ number_format($therapist->earned_commissions, 2) }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $therapist->completed_appointments_count }} visits completed</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">No therapist data recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointments Today Table & Low Stock Alerts -->
<div class="row g-3">
    <!-- Today's Appointments -->
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar2-range text-primary me-2"></i>Today's Appointment Schedule</h6>
                    <small class="text-muted">{{ date('l, F j, Y') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-plus-lg me-1"></i>Book
                    </a>
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Services</th>
                                <th>Therapist / Room</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayAppointments as $apt)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ date('h:i A', strtotime($apt->start_time)) }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ date('h:i A', strtotime($apt->end_time)) }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('customers.show', $apt->customer) }}" class="text-decoration-none fw-semibold text-dark small">
                                            {{ $apt->customer->name }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $apt->customer->phone }}</div>
                                    </td>
                                    <td>
                                        @foreach($apt->items as $item)
                                            <span class="badge bg-light text-dark border me-1">{{ $item->service->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $apt->therapist?->name ?? 'Unassigned' }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $apt->room?->name ?? 'Open Station' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $apt->status_badge_class }} text-uppercase" style="font-size: 0.7rem;">
                                            {{ str_replace('_', ' ', $apt->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Manage
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li><a class="dropdown-item" href="{{ route('appointments.show', $apt) }}"><i class="bi bi-eye me-2"></i>Details</a></li>
                                                @if($apt->status !== 'completed' && $apt->status !== 'cancelled')
                                                    <li>
                                                        <a class="dropdown-item text-primary" href="{{ route('pos.terminal', ['appointment_id' => $apt->id]) }}">
                                                            <i class="bi bi-cash-coin me-2"></i>Checkout at POS
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        <i class="bi bi-calendar-x fs-3 d-block mb-2 text-secondary"></i>
                                        No appointments scheduled for today yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Inventory Alerts -->
    <div class="col-12 col-lg-4">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Low Stock Watchlist</h6>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="small text-decoration-none">Manage &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($lowStockProducts as $prod)
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="fw-semibold small text-dark">{{ $prod->name }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">SKU: {{ $prod->sku }} &bull; Brand: {{ $prod->brand ?? 'Botanical Reserve' }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">
                                    {{ $prod->stock_quantity }} {{ $prod->unit }}s left
                                </span>
                                <div class="text-muted" style="font-size: 0.7rem;">Threshold: {{ $prod->low_stock_threshold }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                            All inventory items are currently well stocked.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueTrendChart');
    if (!ctx) return;

    const labels = {!! json_encode($chartLabels) !!};
    const dataValues = {!! json_encode($chartRevenues) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Gross Daily Revenue ($)',
                data: dataValues,
                borderColor: '#c59b27',
                backgroundColor: 'rgba(197, 155, 39, 0.1)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#1e2532',
                pointBorderColor: '#c59b27',
                pointHoverRadius: 6,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Revenue: $' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
