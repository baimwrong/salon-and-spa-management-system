@extends('layouts.app')

@section('title', 'Services & Treatment Menu')
@section('page_title', 'Services & Treatment Catalog')

@section('content')
<div class="card aura-card">
    <div class="aura-card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('services.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search service or code..." value="{{ request('search') }}">
                </div>

                <select name="category_id" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->services_count }})
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                    <i class="bi bi-folder-plus me-1"></i>New Category
                </button>
                <a href="{{ route('services.create') }}" class="btn btn-sm btn-aura-gold">
                    <i class="bi bi-plus-lg me-1"></i>Add Service
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Code</th>
                        <th>Treatment / Service Name</th>
                        <th>Category</th>
                        <th>Duration + Buffer</th>
                        <th>Room / Suite</th>
                        <th class="text-end">Service Fee</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $s)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold small">{{ $s->code }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $s->name }}</div>
                                @if($s->description)
                                    <div class="text-muted text-truncate small" style="max-width: 320px; font-size: 0.72rem;">{{ $s->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-soft-primary small text-primary"><i class="bi {{ $s->category->icon }} me-1"></i>{{ $s->category->name }}</span>
                            </td>
                            <td class="small">
                                <span class="fw-semibold">{{ $s->duration_minutes }}m</span>
                                <span class="text-muted" style="font-size: 0.7rem;">(+{{ $s->buffer_time_minutes }}m buffer)</span>
                            </td>
                            <td>
                                @if($s->requires_room)
                                    <span class="badge bg-info text-dark small"><i class="bi bi-door-closed me-1"></i>Private Suite</span>
                                @else
                                    <span class="badge bg-light text-muted border small">Open Chair</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ${{ number_format($s->price, 2) }}
                            </td>
                            <td>
                                @if($s->is_active)
                                    <span class="badge bg-success small">Active</span>
                                @else
                                    <span class="badge bg-secondary small">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('services.edit', $s) }}" class="btn btn-light border" title="Edit Service">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('services.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete service {{ $s->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border text-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="bi bi-scissors fs-2 d-block mb-2 text-secondary"></i>
                                No services found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($services->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $services->links() }}
        </div>
    @endif
</div>

<!-- Modal: New Category -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('services.categories.store') }}">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-folder-plus text-warning me-2"></i>Add Service Category</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Body Wraps & Scrubs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Icon Class</label>
                        <select name="icon" class="form-select">
                            <option value="bi-scissors">Scissors (Hair)</option>
                            <option value="bi-stars">Stars (Facials / Beauty)</option>
                            <option value="bi-heart-pulse">Heart (Massage / Wellness)</option>
                            <option value="bi-gem">Gem (Nails / Lashes)</option>
                            <option value="bi-flower1">Flower (Signature Spa Packages)</option>
                            <option value="bi-droplet">Droplet (Hydrotherapy)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief overview of category treatments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-aura-primary">Create Category</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
