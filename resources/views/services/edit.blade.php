@extends('layouts.app')

@section('title', 'Edit Service: ' . $service->name)
@section('page_title', 'Edit Service Offering')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editing: {{ $service->name }} ({{ $service->code }})</h6>
                <a href="{{ route('services.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('services.update', $service) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Service Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $service->code) }}" required>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Service / Treatment Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" step="5" min="5" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Buffer Time (Min) <span class="text-danger">*</span></label>
                            <input type="number" step="5" min="0" name="buffer_time_minutes" class="form-control" value="{{ old('buffer_time_minutes', $service->buffer_time_minutes) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Client Service Fee ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.50" min="0" name="price" class="form-control" value="{{ old('price', $service->price) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Consumables / Cost Price ($)</label>
                            <input type="number" step="0.50" min="0" name="cost_price" class="form-control" value="{{ old('cost_price', $service->cost_price) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="requires_room" id="requires_room" value="1" {{ old('requires_room', $service->requires_room) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="requires_room">Requires Private Treatment Suite</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="is_active">Active & Available on Booking Menu</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('services.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-primary px-4 fw-bold">
                        Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
