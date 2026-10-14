@extends('layouts.app')

@section('title', 'Add New Inventory Product')
@section('page_title', 'Create Product Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam text-warning me-2"></i>Product Specifications</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('products.store') }}">
                @csrf
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" required placeholder="e.g. PRD-SK-010">
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Peptide Hydrating Serum 50ml">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Brand / Manufacturer</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="e.g. Lumière Derma">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Usage Purpose <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="retail" {{ old('type') === 'retail' ? 'selected' : '' }}>Retail Only (For Sale to Clients)</option>
                                <option value="backbar_consumable" {{ old('type') === 'backbar_consumable' ? 'selected' : '' }}>Backbar Only (Used in Treatments)</option>
                                <option value="both" {{ old('type', 'both') === 'both' ? 'selected' : '' }}>Both (Retail & In-Salon)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Retail Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.50" min="0" name="retail_price" class="form-control" value="{{ old('retail_price') }}" required placeholder="0.00">
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Cost Price ($)</label>
                            <input type="number" step="0.50" min="0" name="cost_price" class="form-control" value="{{ old('cost_price', '0.00') }}" placeholder="0.00">
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Initial Stock Balance <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 10) }}" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Low Stock Threshold <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', 5) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Packaging Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select">
                                <option value="bottle">Bottle</option>
                                <option value="jar">Jar</option>
                                <option value="tube">Tube</option>
                                <option value="box">Box</option>
                                <option value="piece">Piece</option>
                                <option value="kit">Kit</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Ingredients</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Key active ingredients, instructions for retail use..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-gold px-4 fw-bold">
                        Add to Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
