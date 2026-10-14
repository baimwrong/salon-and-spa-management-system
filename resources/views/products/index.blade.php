@extends('layouts.app')

@section('title', 'Inventory & Retail Products')
@section('page_title', 'Inventory & Retail Stock Management')

@section('content')
<div class="card aura-card">
    <div class="aura-card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('products.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search SKU, name, brand..." value="{{ request('search') }}">
                </div>

                <select name="category_id" class="form-select form-select-sm" style="max-width: 180px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </option>
                    @endforeach
                </select>

                <select name="type" class="form-select form-select-sm" style="max-width: 160px;" onchange="this.form.submit()">
                    <option value="">All Usages</option>
                    <option value="retail" {{ request('type') === 'retail' ? 'selected' : '' }}>Retail Only</option>
                    <option value="backbar_consumable" {{ request('type') === 'backbar_consumable' ? 'selected' : '' }}>Backbar Consumable</option>
                    <option value="both" {{ request('type') === 'both' ? 'selected' : '' }}>Dual (Retail & In-Salon)</option>
                </select>

                <div class="form-check form-check-inline small mb-0 ms-1">
                    <input class="form-check-input" type="checkbox" name="low_stock" id="lowStockCheck" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label text-danger fw-semibold" for="lowStockCheck">Low Stock Only ({{ $lowStockTotal }})</label>
                </div>
            </form>

            <a href="{{ route('products.create') }}" class="btn btn-sm btn-aura-gold">
                <i class="bi bi-plus-lg me-1"></i>Add Product
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>SKU</th>
                        <th>Product Name & Brand</th>
                        <th>Category</th>
                        <th>Usage Type</th>
                        <th>Cost</th>
                        <th>Retail Price</th>
                        <th>Stock Level</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border small fw-bold">{{ $p->sku }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $p->name }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Brand: {{ $p->brand ?? 'Botanical Reserve' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">{{ $p->category?->name ?? 'General' }}</span>
                            </td>
                            <td>
                                @if($p->type === 'retail')
                                    <span class="badge bg-soft-info text-primary small">Retail</span>
                                @elseif($p->type === 'backbar_consumable')
                                    <span class="badge bg-soft-warning text-dark small">Backbar / Salon</span>
                                @else
                                    <span class="badge bg-soft-success text-success small">Retail & Salon</span>
                                @endif
                            </td>
                            <td class="small text-muted">${{ number_format($p->cost_price, 2) }}</td>
                            <td class="fw-bold small text-dark">${{ number_format($p->retail_price, 2) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $p->isLowStock() ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                                        {{ $p->stock_quantity }} {{ $p->unit }}
                                    </span>
                                    @if($p->isLowStock())
                                        <i class="bi bi-exclamation-triangle-fill text-danger small" title="Low stock! Below threshold of {{ $p->low_stock_threshold }}"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#restockModal{{ $p->id }}" title="Restock / Adjust">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>Stock
                                    </button>
                                    <a href="{{ route('products.edit', $p) }}" class="btn btn-light border" title="Edit Product">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>

                                <!-- Restock Modal for this Product -->
                                <div class="modal fade text-start" id="restockModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="POST" action="{{ route('products.restock', $p) }}">
                                            @csrf
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">
                                                        <i class="bi bi-box-seam text-warning me-2"></i>Stock Adjustment: {{ $p->name }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-light border small mb-3">
                                                        Current Balance: <strong>{{ $p->stock_quantity }} {{ $p->unit }}s</strong> &bull; Low Stock Alert: <strong>{{ $p->low_stock_threshold }}</strong>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Action / Movement Type</label>
                                                        <select name="type" class="form-select" required>
                                                            <option value="restock">Receive Shipment / Restock (+)</option>
                                                            <option value="service_usage">In-Salon Treatment Consumption (-)</option>
                                                            <option value="damage">Damaged / Expired Product (-)</option>
                                                            <option value="adjustment">Inventory Audit Correction</option>
                                                        </select>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Quantity</label>
                                                            <input type="number" name="quantity" class="form-control" min="1" required value="10">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Unit Cost ($)</label>
                                                            <input type="number" step="0.50" min="0" name="unit_cost" class="form-control" value="{{ $p->cost_price }}">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Supplier Invoice / PO Reference #</label>
                                                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. PO-8921">
                                                    </div>

                                                    <div class="mb-0">
                                                        <label class="form-label small fw-semibold">Audit Notes</label>
                                                        <input type="text" name="notes" class="form-control" placeholder="Reason for change...">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-aura-primary">Save Adjustment</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="bi bi-box-seam fs-2 d-block mb-2 text-secondary"></i>
                                No inventory items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
