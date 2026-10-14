@extends('layouts.app')

@section('title', 'Edit Product: ' . $product->name)
@section('page_title', 'Edit Inventory Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editing: {{ $product->name }} ({{ $product->sku }})</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Brand / Manufacturer</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Usage Purpose <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="retail" {{ old('type', $product->type) === 'retail' ? 'selected' : '' }}>Retail Only</option>
                                <option value="backbar_consumable" {{ old('type', $product->type) === 'backbar_consumable' ? 'selected' : '' }}>Backbar Only</option>
                                <option value="both" {{ old('type', $product->type) === 'both' ? 'selected' : '' }}>Both (Retail & In-Salon)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Retail Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.50" min="0" name="retail_price" class="form-control" value="{{ old('retail_price', $product->retail_price) }}" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Cost Price ($)</label>
                            <input type="number" step="0.50" min="0" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}">
                        </div>

                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-semibold">Low Stock Threshold <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required>
                        </div>

                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-semibold">Packaging Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-primary px-4 fw-bold">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
