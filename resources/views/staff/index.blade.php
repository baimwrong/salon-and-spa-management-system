@extends('layouts.app')

@section('title', 'Staff & Stylist Directory')
@section('page_title', 'Staff & Stylists Roster')

@section('content')
<div class="card aura-card">
    <div class="aura-card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('staff.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search staff by name or email..." value="{{ request('search') }}">
                </div>

                <select name="role" class="form-select form-select-sm" style="max-width: 160px;" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="therapist" {{ request('role') === 'therapist' ? 'selected' : '' }}>Stylists & Therapists</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Receptionists</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Managers</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrators</option>
                </select>

                <select name="status" class="form-select form-select-sm" style="max-width: 140px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </form>

            <a href="{{ route('staff.create') }}" class="btn btn-sm btn-aura-gold">
                <i class="bi bi-person-plus me-1"></i>Add Staff Member
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Staff Member</th>
                        <th>Role</th>
                        <th>Specialization / Title</th>
                        <th>Contact Phone</th>
                        <th class="text-center">Commission %</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $u)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('staff.show', $u) }}" class="fw-semibold text-dark text-decoration-none small">
                                            {{ $u->name }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge bg-danger text-uppercase" style="font-size: 0.7rem;">Admin</span>
                                @elseif($u->role === 'manager')
                                    <span class="badge bg-primary text-uppercase" style="font-size: 0.7rem;">Manager</span>
                                @elseif($u->role === 'receptionist')
                                    <span class="badge bg-success text-uppercase" style="font-size: 0.7rem;">Receptionist</span>
                                @else
                                    <span class="badge bg-warning text-dark text-uppercase" style="font-size: 0.7rem;">Stylist / Therapist</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $u->specialization ?? 'General Staff' }}
                            </td>
                            <td class="small text-muted">{{ $u->phone ?? 'N/A' }}</td>
                            <td class="text-center fw-bold small text-dark">
                                {{ $u->commission_rate }}%
                            </td>
                            <td>
                                <span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-secondary' }} small">
                                    {{ ucfirst(str_replace('_', ' ', $u->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('staff.show', $u) }}" class="btn btn-light border" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('staff.edit', $u) }}" class="btn btn-light border" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="bi bi-person-badge fs-2 d-block mb-2 text-secondary"></i>
                                No staff records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($staff->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $staff->links() }}
        </div>
    @endif
</div>
@endsection
