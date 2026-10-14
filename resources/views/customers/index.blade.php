@extends('layouts.app')

@section('title', 'Client CRM Directory')
@section('page_title', 'Customer Relationship Management (CRM)')

@section('content')
<div class="card aura-card">
    <div class="aura-card-header">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, phone, code or email..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="vip" {{ request('status') === 'vip' ? 'selected' : '' }}>VIP Clients</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="skin_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Skin Types</option>
                    <option value="Sensitive" {{ request('skin_type') === 'Sensitive' ? 'selected' : '' }}>Sensitive</option>
                    <option value="Dry" {{ request('skin_type') === 'Dry' ? 'selected' : '' }}>Dry</option>
                    <option value="Oily" {{ request('skin_type') === 'Oily' ? 'selected' : '' }}>Oily</option>
                    <option value="Combination" {{ request('skin_type') === 'Combination' ? 'selected' : '' }}>Combination</option>
                    <option value="Normal" {{ request('skin_type') === 'Normal' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>

            <div class="col-12 col-md-2 text-md-end">
                <a href="{{ route('customers.create') }}" class="btn btn-sm btn-aura-gold w-100">
                    <i class="bi bi-person-plus me-1"></i>New Client
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Client Details</th>
                        <th>Phone & Email</th>
                        <th>Skin / Hair Profile</th>
                        <th>Preferred Stylist</th>
                        <th>Loyalty Balance</th>
                        <th>Total Spend</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light border text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ substr($c->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('customers.show', $c) }}" class="fw-semibold text-dark text-decoration-none small">
                                            {{ $c->name }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $c->customer_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $c->phone }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $c->email ?? 'No email' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">{{ $c->skin_type ?? 'Unspecified' }}</span>
                                @if($c->allergies)
                                    <span class="badge bg-danger text-white small" title="{{ $c->allergies }}"><i class="bi bi-shield-exclamation"></i> Allergy</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $c->preferredTherapist?->name ?? 'Any' }}
                            </td>
                            <td>
                                <span class="badge bg-soft-gold text-dark fw-bold">
                                    <i class="bi bi-stars text-warning me-1"></i>{{ $c->loyalty_points }} pts
                                </span>
                            </td>
                            <td class="fw-bold small text-dark">
                                ${{ number_format($c->total_spend, 2) }}
                            </td>
                            <td>
                                @if($c->status === 'vip')
                                    <span class="badge bg-warning text-dark text-uppercase" style="font-size: 0.7rem;"><i class="bi bi-gem"></i> VIP</span>
                                @elseif($c->status === 'active')
                                    <span class="badge bg-success text-uppercase" style="font-size: 0.7rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary text-uppercase" style="font-size: 0.7rem;">{{ $c->status }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $c) }}" class="btn btn-light border" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.create', ['customer_id' => $c->id]) }}" class="btn btn-outline-primary" title="Book Appointment">
                                        <i class="bi bi-calendar-plus"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $c) }}" class="btn btn-light border" title="Edit Client">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                                No customer records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
