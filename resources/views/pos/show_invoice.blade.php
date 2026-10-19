@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number)
@section('page_title', 'Invoice Details & Receipt')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <!-- Actions Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('pos.invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Invoices
            </a>

            <div class="d-flex gap-2">
                <a href="{{ route('pos.terminal') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-cart-plus me-1"></i>New Sale
                </a>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="printThermal()">
                    <i class="bi bi-receipt me-1"></i>Thermal Receipt (80mm)
                </button>
                <button type="button" class="btn btn-sm btn-aura-gold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print Invoice
                </button>
            </div>
        </div>

        <!-- Printable Invoice Card -->
        <div class="card aura-card p-4 p-md-5 mb-4" id="printableReceipt">
            <!-- Header & Branding -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-flower1 text-warning fs-3"></i>
                        <h4 class="fw-bold mb-0 text-dark">SALON & SPA</h4>
                    </div>
                    <div class="small text-muted">Wellness & Beauty Center</div>
                    <div class="small text-muted">742 Evergreen Terrace, Beverly Hills, CA</div>
                    <div class="small text-muted">Tel: +1 (555) 019-2831 &bull; info@salonspa.local</div>
                </div>

                <div class="text-end">
                    <h5 class="fw-bold text-dark mb-1">INVOICE</h5>
                    <div class="fw-bold text-primary">{{ $invoice->invoice_number }}</div>
                    <div class="small text-muted mt-1">Date: {{ $invoice->created_at->format('M d, Y h:i A') }}</div>
                    <span class="badge {{ $invoice->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase mt-2">
                        {{ $invoice->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Customer & Cashier Info -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Billed To:</h6>
                    @if($invoice->customer)
                        <div class="fw-bold text-dark">{{ $invoice->customer->name }}</div>
                        <div class="small text-muted">{{ $invoice->customer->phone }}</div>
                        @if($invoice->customer->email)
                            <div class="small text-muted">{{ $invoice->customer->email }}</div>
                        @endif
                        <div class="small text-muted">Loyalty Balance: {{ $invoice->customer->loyalty_points }} pts</div>
                    @else
                        <div class="fw-bold text-dark">Walk-in Guest</div>
                        <div class="small text-muted">Standard Retail Sale</div>
                    @endif
                </div>

                <div class="col-6 text-end">
                    <h6 class="text-muted small text-uppercase fw-bold mb-1">Transaction Details:</h6>
                    <div class="small text-muted">Cashier: <strong class="text-dark">{{ $invoice->cashier?->name ?? 'Front Desk' }}</strong></div>
                    <div class="small text-muted">Payment: <strong class="text-dark text-uppercase">{{ str_replace('_', ' ', $invoice->payment_method) }}</strong></div>
                    @if($invoice->appointment)
                        <div class="small text-muted">Linked Booking: <strong class="text-dark">{{ $invoice->appointment->appointment_number }}</strong></div>
                    @endif
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Item Description</th>
                            <th>Staff / Stylist</th>
                            <th class="text-center" style="width: 70px;">Qty</th>
                            <th class="text-end" style="width: 120px;">Price</th>
                            <th class="text-end" style="width: 130px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                    <span class="badge {{ $item->item_type === 'service' ? 'bg-light text-primary border' : 'bg-light text-success border' }}" style="font-size: 0.65rem;">
                                        {{ strtoupper($item->item_type) }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $item->therapist?->name ?? 'N/A' }}
                                </td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals Breakdown -->
            <div class="row justify-content-end mb-4">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">${{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    @if($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between py-1 small text-danger">
                            <span>Discount:</span>
                            <span>-${{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-1 small text-muted">
                        <span>Tax ({{ $invoice->tax_rate }}%):</span>
                        <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>

                    @if($invoice->tip_amount > 0)
                        <div class="d-flex justify-content-between py-1 small text-muted">
                            <span>Tip / Gratuity:</span>
                            <span>${{ number_format($invoice->tip_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-2 border-top border-bottom my-2">
                        <span class="fw-bold fs-5">Grand Total:</span>
                        <span class="fw-bold fs-5 text-dark">${{ number_format($invoice->total_amount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-1 small text-muted">
                        <span>Amount Paid:</span>
                        <span class="fw-semibold text-dark">${{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>

                    @if($invoice->change_amount > 0)
                        <div class="d-flex justify-content-between py-1 small text-success">
                            <span>Change Given:</span>
                            <span class="fw-bold">${{ number_format($invoice->change_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($invoice->notes)
                <div class="p-3 bg-light rounded-3 small text-muted mb-4">
                    <strong>Notes:</strong> {{ $invoice->notes }}
                </div>
            @endif

            <div class="text-center pt-3 border-top text-muted small">
                <div class="fw-semibold text-dark">Thank you for visiting our Salon & Spa!</div>
                <div>We look forward to welcoming you back soon.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .aura-sidebar, .aura-topbar, .no-print, nav, .btn, header {
        display: none !important;
    }
    .aura-main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    #printableReceipt {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
    }
    body.print-thermal #printableReceipt {
        max-width: 78mm !important;
        width: 78mm !important;
        font-size: 11px !important;
        padding: 4px !important;
    }
    body.print-thermal .table th,
    body.print-thermal .table td {
        padding: 4px 2px !important;
        font-size: 11px !important;
    }
    body.print-thermal h4 {
        font-size: 16px !important;
    }
    body.print-thermal h5 {
        font-size: 14px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function printThermal() {
    document.body.classList.add('print-thermal');
    window.print();
    setTimeout(() => {
        document.body.classList.remove('print-thermal');
    }, 1500);
}
</script>
@endpush

