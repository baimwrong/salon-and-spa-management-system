@extends('layouts.app')

@section('title', 'Invoices & Billing History')
@section('page_title', 'Invoices & Billing History')

@section('content')
<div class="card aura-card">
    <div class="aura-card-header">
        <form method="GET" action="{{ route('pos.invoices.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search invoice # or client..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Payment Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="debit_card" {{ request('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>
            <div class="col-12 col-md-2 text-md-end">
                <a href="{{ route('pos.terminal') }}" class="btn btn-sm btn-aura-gold w-100">
                    <i class="bi bi-plus-lg me-1"></i>New Sale
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date & Time</th>
                        <th>Client</th>
                        <th>Cashier</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('pos.invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none small">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="small text-muted">
                                {{ $inv->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                @if($inv->customer)
                                    <a href="{{ route('customers.show', $inv->customer) }}" class="text-decoration-none text-dark fw-semibold small">
                                        {{ $inv->customer->name }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $inv->customer->phone }}</div>
                                @else
                                    <span class="text-muted small">Walk-in Guest</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $inv->cashier?->name ?? 'System' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $inv->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $inv->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ $inv->payment_status }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ${{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('pos.invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-receipt me-1"></i>Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="bi bi-receipt fs-2 d-block mb-2 text-secondary"></i>
                                No invoice records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
