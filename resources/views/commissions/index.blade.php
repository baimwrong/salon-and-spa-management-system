@extends('layouts.app')

@section('title', 'Therapist Commission Ledger')
@section('page_title', 'Staff Commission Settlements')

@section('content')
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Total Pending Settlements</div>
                <h3 class="fw-bold my-1 text-warning">${{ number_format($totalPending, 2) }}</h3>
                <div class="small text-muted">Awaiting payroll / distribution</div>
            </div>
            <div class="aura-stat-icon aura-icon-gold">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="aura-stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-semibold text-uppercase">Total Disbursed Commissions</div>
                <h3 class="fw-bold my-1 text-success">${{ number_format($totalPaid, 2) }}</h3>
                <div class="small text-muted">Paid to specialists</div>
            </div>
            <div class="aura-stat-icon aura-icon-green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="card aura-card">
    <div class="aura-card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('commissions.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <select name="therapist_id" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">All Therapists</option>
                    @foreach($therapists as $t)
                        <option value="{{ $t->id }}" {{ request('therapist_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->commission_rate }}%)
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-select form-select-sm" style="max-width: 160px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Payout</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Disbursed / Paid</option>
                </select>
            </form>

            <button type="button" class="btn btn-sm btn-aura-primary" data-bs-toggle="modal" data-bs-target="#bulkSettleModal">
                <i class="bi bi-wallet2 me-1"></i>Bulk Settle Pending
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Date</th>
                        <th>Therapist / Stylist</th>
                        <th>Invoice & Item</th>
                        <th class="text-end">Commission Amount</th>
                        <th>Status</th>
                        <th>Payout Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                        <tr>
                            <td class="small text-muted">{{ $p->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('staff.show', $p->therapist) }}" class="fw-semibold text-dark text-decoration-none small">
                                    {{ $p->therapist->name }}
                                </a>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $p->therapist->specialization ?? 'Stylist' }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $p->invoiceItem?->item_name ?? 'POS Service' }}</div>
                                @if($p->invoiceItem?->invoice)
                                    <a href="{{ route('pos.invoices.show', $p->invoiceItem->invoice) }}" class="text-decoration-none small text-muted" style="font-size: 0.72rem;">
                                        {{ $p->invoiceItem->invoice->invoice_number }}
                                    </a>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">
                                ${{ number_format($p->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $p->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $p->payout_date ? $p->payout_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="text-end">
                                @if($p->status === 'pending')
                                    <form method="POST" action="{{ route('commissions.markPaid', $p) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this commission as paid?')">
                                            <i class="bi bi-check-lg me-1"></i>Pay
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check2-all text-success"></i> Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="bi bi-graph-up-arrow fs-2 d-block mb-2 text-secondary"></i>
                                No commission records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($payouts->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $payouts->links() }}
        </div>
    @endif
</div>

<!-- Bulk Settle Modal -->
<div class="modal fade" id="bulkSettleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('commissions.markAllPaid') }}">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-wallet2 text-warning me-2"></i>Bulk Settle Pending Commissions</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Specialist to Settle</label>
                        <select name="therapist_id" class="form-select" required>
                            @foreach($therapists as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->commission_rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-light border small mb-0">
                        This action will mark all pending commissions for the selected therapist as <strong>PAID</strong> with today's date timestamp.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-aura-primary">Confirm Settlement</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
