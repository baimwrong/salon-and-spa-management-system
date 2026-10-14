@extends('layouts.app')

@section('title', 'Admin Bulk Data Import Hub')
@section('page_title', 'Bulk Data Ingestion & Import Center')

@section('content')
<div class="row g-4">
    <!-- Top: Entity Templates & Quick Download Cards -->
    <div class="col-12">
        <div class="row g-3">
            <!-- Customers Template Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card aura-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="aura-stat-icon aura-icon-rose" style="width: 36px; height: 36px; font-size: 1rem;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Clients / Customers</h6>
                            <small class="text-muted">CRM Profiles & Allergies</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 0.78rem;">
                        Import client profiles, skin/hair diagnostics, allergy notes, loyalty balances, and contact info.
                    </p>
                    <a href="{{ route('admin.import.sample', 'customers') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                        <i class="bi bi-download me-1"></i>Sample Template (.csv)
                    </a>
                </div>
            </div>

            <!-- Services Template Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card aura-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="aura-stat-icon aura-icon-gold" style="width: 36px; height: 36px; font-size: 1rem;">
                            <i class="bi bi-scissors"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Service Catalog</h6>
                            <small class="text-muted">Treatments & Packages</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 0.78rem;">
                        Import treatments, categories, duration, buffer times, client pricing, and room requirements.
                    </p>
                    <a href="{{ route('admin.import.sample', 'services') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                        <i class="bi bi-download me-1"></i>Sample Template (.csv)
                    </a>
                </div>
            </div>

            <!-- Products Template Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card aura-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="aura-stat-icon aura-icon-green" style="width: 36px; height: 36px; font-size: 1rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Inventory & Retail</h6>
                            <small class="text-muted">Stock & Consumables</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 0.78rem;">
                        Import retail and in-salon backbar products, SKUs, cost prices, retail fees, and initial stock quantities.
                    </p>
                    <a href="{{ route('admin.import.sample', 'products') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                        <i class="bi bi-download me-1"></i>Sample Template (.csv)
                    </a>
                </div>
            </div>

            <!-- Staff Template Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card aura-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="aura-stat-icon aura-icon-blue" style="width: 36px; height: 36px; font-size: 1rem;">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Staff & Stylists</h6>
                            <small class="text-muted">Employees & Roles</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 0.78rem;">
                        Import stylists, therapists, managers, role levels, commission rates, and professional specializations.
                    </p>
                    <a href="{{ route('admin.import.sample', 'staff') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                        <i class="bi bi-download me-1"></i>Sample Template (.csv)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Box Form -->
    <div class="col-12 col-lg-5">
        <div class="card aura-card h-100">
            <div class="aura-card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-cloud-arrow-up text-warning me-2"></i>Upload Import File</h6>
            </div>

            <form method="POST" action="{{ route('admin.import.process') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Entity Type <span class="text-danger">*</span></label>
                        <select name="import_type" class="form-select" required>
                            <option value="customers">Customers / Client CRM Records</option>
                            <option value="services">Services & Treatment Menu</option>
                            <option value="products">Products & Inventory Stock</option>
                            <option value="staff">Staff & Specialists</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
                        <div class="form-text small">Accepted format: standard CSV (comma-separated, UTF-8). Max size: 10MB.</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1" checked>
                        <label class="form-check-label small" for="update_existing">
                            <strong>Update existing records</strong> if matched by phone/SKU/code/email
                        </label>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        The import engine executes in an atomic database transaction. Any structural faults will be safely rolled back and logged.
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 text-end">
                    <button type="submit" class="btn btn-aura-primary px-4 fw-semibold">
                        <i class="bi bi-upload me-1"></i>Process Bulk Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Recent Import Logs & Audit -->
    <div class="col-12 col-lg-7">
        <div class="card aura-card h-100">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Recent Import Logs</h6>
                <span class="badge bg-light text-dark border">Audit Trail</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Date & Admin</th>
                                <th>Entity</th>
                                <th>File Name</th>
                                <th class="text-center">Success / Failed</th>
                                <th>Status</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $log->created_at->format('M d, Y h:i A') }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">By: {{ $log->user?->name ?? 'Admin' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                            {{ $log->import_type }}
                                        </span>
                                    </td>
                                    <td class="small text-truncate" style="max-width: 140px;">
                                        {{ $log->file_name }}
                                    </td>
                                    <td class="text-center small">
                                        <span class="text-success fw-bold">{{ $log->successful_rows }}</span> /
                                        <span class="{{ $log->failed_rows > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $log->failed_rows }}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">({{ $log->total_rows }})</span>
                                    </td>
                                    <td>
                                        @if($log->status === 'completed')
                                            <span class="badge bg-success small">Completed</span>
                                        @elseif($log->status === 'partial')
                                            <span class="badge bg-warning text-dark small">Partial</span>
                                        @else
                                            <span class="badge bg-danger small">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($log->errors && count($log->errors) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#errorLogModal{{ $log->id }}">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Errors
                                            </button>

                                            <!-- Modal for Error Log -->
                                            <div class="modal fade text-start" id="errorLogModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content border-0 shadow">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold text-danger">
                                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Import Error Details: {{ $log->file_name }}
                                                            </h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-0">
                                                            <div class="table-responsive" style="max-height: 350px;">
                                                                <table class="table table-sm table-striped mb-0 small">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th style="width: 100px;">Line / Row</th>
                                                                            <th>Error Description</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($log->errors as $err)
                                                                            <tr>
                                                                                <td class="fw-bold">{{ $err['line'] ?? 'N/A' }}</td>
                                                                                <td class="text-danger">{{ $err['error'] ?? 'Unknown' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small"><i class="bi bi-check2 text-success"></i> Clean</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        No bulk imports recorded yet. Download a sample CSV template on the left to start!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($logs->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
