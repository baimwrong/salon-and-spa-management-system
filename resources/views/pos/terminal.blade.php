@extends('layouts.app')

@section('title', 'Front Desk POS Terminal')
@section('page_title', 'Point of Sale (POS) Terminal')

@section('content')
<div class="row g-3">
    <!-- Left: Catalog (Services & Retail Products) -->
    <div class="col-12 col-lg-7">
        <div class="card aura-card h-100">
            <div class="aura-card-header pb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <ul class="nav nav-pills" id="posCatalogTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active btn-sm" id="services-tab" data-bs-toggle="pill" data-bs-target="#services-pane" type="button">
                                <i class="bi bi-scissors me-1"></i> Treatments & Services
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-sm" id="products-tab" data-bs-toggle="pill" data-bs-target="#products-pane" type="button">
                                <i class="bi bi-box-seam me-1"></i> Retail Products
                            </button>
                        </li>
                    </ul>

                    <div class="input-group input-group-sm" style="max-width: 240px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="catalogSearch" class="form-control" placeholder="Search item...">
                    </div>
                </div>
            </div>

            <div class="card-body p-3 pos-catalog-scroll">
                <div class="tab-content" id="posCatalogContent">
                    <!-- Services Tab Pane -->
                    <div class="tab-pane fade show active" id="services-pane" role="tabpanel">
                        <div class="row g-2" id="servicesGrid">
                            @foreach($services as $svc)
                                <div class="col-12 col-sm-6 col-md-4 catalog-item" data-name="{{ strtolower($svc->name) }}" data-category="{{ strtolower($svc->category->name) }}">
                                    <div class="card p-2 h-100 pos-item-card rounded-3" onclick="addToCart('service', {{ $svc->id }}, '{{ addslashes($svc->name) }}', {{ $svc->price }})">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-light text-dark border small">{{ $svc->category->name }}</span>
                                            <span class="fw-bold text-dark">${{ number_format($svc->price, 2) }}</span>
                                        </div>
                                        <div class="fw-semibold text-truncate small mb-1">{{ $svc->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.72rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $svc->duration_minutes }}m (+{{ $svc->buffer_time_minutes }}m buffer)
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Products Tab Pane -->
                    <div class="tab-pane fade" id="products-pane" role="tabpanel">
                        <div class="row g-2" id="productsGrid">
                            @foreach($products as $prod)
                                <div class="col-12 col-sm-6 col-md-4 catalog-item" data-name="{{ strtolower($prod->name) }}" data-brand="{{ strtolower($prod->brand) }}">
                                    <div class="card p-2 h-100 pos-item-card rounded-3" onclick="addToCart('product', {{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->retail_price }})">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-light text-dark border small">{{ $prod->brand ?? 'Retail' }}</span>
                                            <span class="fw-bold text-dark">${{ number_format($prod->retail_price, 2) }}</span>
                                        </div>
                                        <div class="fw-semibold text-truncate small mb-1">{{ $prod->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.72rem;">
                                            <i class="bi bi-box me-1"></i>Stock: {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Order Ticket & Checkout Form -->
    <div class="col-12 col-lg-5">
        <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
            @csrf
            @if($selectedAppointment)
                <input type="hidden" name="appointment_id" value="{{ $selectedAppointment->id }}">
            @endif

            <div class="card aura-card">
                <div class="aura-card-header p-3 bg-light bg-opacity-50">
                    <!-- Client Selector -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-uppercase"><i class="bi bi-receipt me-1"></i>Order Ticket</span>
                        @if($selectedAppointment)
                            <span class="badge bg-info text-dark">Linked: {{ $selectedAppointment->appointment_number }}</span>
                        @endif
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Customer / Guest</label>
                        <select name="customer_id" id="customerSelect" class="form-select form-select-sm">
                            <option value="">-- Walk-in Guest (No Profile) --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ ($selectedAppointment && $selectedAppointment->customer_id == $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone }}) - Points: {{ $c->loyalty_points }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Cart Items List -->
                <div class="card-body p-3 pos-cart-scroll" id="cartContainer">
                    <div id="cartItemsList">
                        <!-- Dynamic Cart Items Injected via JS -->
                    </div>
                    <div id="emptyCartNotice" class="text-center py-5 text-muted small">
                        <i class="bi bi-bag-plus fs-2 text-secondary d-block mb-2"></i>
                        Select services or products from the left to start sale ticket.
                    </div>
                </div>

                <!-- Bill Totals & Payment Section -->
                <div class="card-footer bg-white border-top p-3">
                    <div class="row g-2 mb-2 small">
                        <div class="col-6">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold float-end" id="displaySubtotal">$0.00</span>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Discount ($):</span>
                                <input type="number" step="0.50" min="0" name="discount_amount" id="discountInput" class="form-control form-control-sm text-end p-1" style="width: 75px;" value="0.00">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Tax (%):</span>
                                <input type="number" step="0.50" min="0" max="100" name="tax_rate" id="taxRateInput" class="form-control form-control-sm text-end p-1" style="width: 75px;" value="10.00">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Tip ($):</span>
                                <input type="number" step="1.00" min="0" name="tip_amount" id="tipInput" class="form-control form-control-sm text-end p-1" style="width: 75px;" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom my-2">
                        <span class="fw-bold fs-5">Total Due:</span>
                        <span class="fw-bold fs-4 text-dark" id="displayTotal">$0.00</span>
                    </div>

                    <!-- Payment Method & Tender Amount -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer / QRIS</option>
                                <option value="loyalty_points">Loyalty Points</option>
                                <option value="split">Split Payment</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">Amount Tendered ($)</label>
                            <input type="number" step="0.01" min="0" name="paid_amount" id="paidAmountInput" class="form-control form-control-sm fw-bold text-end" value="0.00" required>
                        </div>
                        <div class="col-12 text-end">
                            <span class="text-muted small">Change to return: </span>
                            <span class="fw-bold text-success small" id="displayChange">$0.00</span>
                        </div>
                    </div>

                    <button type="submit" id="btnCheckout" class="btn btn-aura-gold w-100 py-2 fw-bold shadow-sm" disabled>
                        <i class="bi bi-check2-circle me-1"></i> Process Payment & Print Receipt
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
const therapistsList = {!! json_encode($therapists) !!};
const preloadedApt = {!! json_encode($selectedAppointment) !!};

document.addEventListener('DOMContentLoaded', function () {
    // If opened with preloaded appointment, populate cart
    if (preloadedApt && preloadedApt.items) {
        preloadedApt.items.forEach(item => {
            addToCart('service', item.service_id, item.service.name, parseFloat(item.price), preloadedApt.therapist_id);
        });
    }

    // Catalog search filter
    document.getElementById('catalogSearch').addEventListener('input', function(e) {
        const val = e.target.value.toLowerCase();
        document.querySelectorAll('.catalog-item').forEach(el => {
            const text = (el.dataset.name || '') + ' ' + (el.dataset.category || '') + ' ' + (el.dataset.brand || '');
            el.style.display = text.includes(val) ? '' : 'none';
        });
    });

    // Tax, Tip, Discount, Tendered inputs
    ['discountInput', 'taxRateInput', 'tipInput'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateTotals);
    });

    document.getElementById('paidAmountInput').addEventListener('input', calculateChange);
});

function addToCart(type, id, name, price, therapistId = null) {
    const existingIndex = cart.findIndex(item => item.type === type && item.id === id);
    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({
            type: type,
            id: id,
            name: name,
            price: price,
            quantity: 1,
            therapist_id: therapistId
        });
    }
    renderCart();
}

function updateCartQuantity(index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function removeCartItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateItemTherapist(index, tId) {
    cart[index].therapist_id = tId ? parseInt(tId) : null;
}

function renderCart() {
    const container = document.getElementById('cartItemsList');
    const notice = document.getElementById('emptyCartNotice');
    const btn = document.getElementById('btnCheckout');

    container.innerHTML = '';

    if (cart.length === 0) {
        notice.style.display = 'block';
        btn.disabled = true;
        updateTotals();
        return;
    }

    notice.style.display = 'none';
    btn.disabled = false;

    cart.forEach((item, index) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'border-bottom py-2';

        let therapistOptions = '<option value="">-- No Commission --</option>';
        therapistsList.forEach(t => {
            const selected = item.therapist_id === t.id ? 'selected' : '';
            therapistOptions += `<option value="${t.id}" ${selected}>${t.name} (${t.commission_rate}%)</option>`;
        });

        itemDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <span class="badge ${item.type === 'service' ? 'bg-primary' : 'bg-success'} me-1" style="font-size: 0.65rem;">
                        ${item.type.toUpperCase()}
                    </span>
                    <span class="fw-semibold small">${item.name}</span>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="removeCartItem(${index})">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-light border px-2 py-0" onclick="updateCartQuantity(${index}, -1)">-</button>
                    <span class="small fw-bold px-1">${item.quantity}</span>
                    <button type="button" class="btn btn-sm btn-light border px-2 py-0" onclick="updateCartQuantity(${index}, 1)">+</button>
                    <span class="text-muted small ms-1">&times; $${item.price.toFixed(2)}</span>
                </div>
                <div class="fw-bold small">$${(item.price * item.quantity).toFixed(2)}</div>
            </div>

            <div class="mt-1 d-flex align-items-center gap-1">
                <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-person me-1"></i>Therapist:</span>
                <select class="form-select form-select-sm py-0" style="font-size: 0.72rem; height: 24px;" onchange="updateItemTherapist(${index}, this.value)">
                    ${therapistOptions}
                </select>
            </div>

            <!-- Hidden inputs for form POST -->
            <input type="hidden" name="items[${index}][type]" value="${item.type}">
            <input type="hidden" name="items[${index}][id]" value="${item.id}">
            <input type="hidden" name="items[${index}][name]" value="${item.name}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${index}][price]" value="${item.price}">
            <input type="hidden" name="items[${index}][therapist_id]" value="${item.therapist_id || ''}">
        `;

        container.appendChild(itemDiv);
    });

    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    cart.forEach(item => {
        subtotal += (item.price * item.quantity);
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const taxRate = parseFloat(document.getElementById('taxRateInput').value) || 0;
    const tip = parseFloat(document.getElementById('tipInput').value) || 0;

    const taxableAmount = Math.max(0, subtotal - discount);
    const taxAmount = taxableAmount * (taxRate / 100);
    const total = taxableAmount + taxAmount + tip;

    document.getElementById('displaySubtotal').innerText = '$' + subtotal.toFixed(2);
    document.getElementById('displayTotal').innerText = '$' + total.toFixed(2);

    const paidInput = document.getElementById('paidAmountInput');
    if (parseFloat(paidInput.value) === 0 || paidInput.dataset.touched !== 'true') {
        paidInput.value = total.toFixed(2);
    }

    calculateChange();
}

function calculateChange() {
    const totalText = document.getElementById('displayTotal').innerText.replace('$', '');
    const total = parseFloat(totalText) || 0;
    const paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    const change = Math.max(0, paid - total);

    document.getElementById('displayChange').innerText = '$' + change.toFixed(2);
}
</script>
@endpush
