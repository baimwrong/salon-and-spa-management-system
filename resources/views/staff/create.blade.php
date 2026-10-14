@extends('layouts.app')

@section('title', 'Add New Staff Member')
@section('page_title', 'Register Staff / Stylist')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus text-warning me-2"></i>Staff Credentials & Role</h6>
                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('staff.store') }}">
                @csrf
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Natasha Romanov">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email Address (Login) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="natasha@salonspa.local">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">System Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="therapist" {{ old('role') === 'therapist' ? 'selected' : '' }}>Stylist / Therapist (Service Provider)</option>
                                <option value="receptionist" {{ old('role') === 'receptionist' ? 'selected' : '' }}>Receptionist (Front Desk POS & Booking)</option>
                                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Salon Operations Manager</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>System Administrator</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Phone Contact</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Specialization / Title</label>
                            <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="e.g. Master Balayage Colorist">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Service Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.50" min="0" max="100" name="commission_rate" class="form-control" value="{{ old('commission_rate', '15.00') }}" required>
                            <div class="text-muted small mt-1">Percentage earned per completed service in POS.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Employment Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active Staff</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Bio / Professional Certifications</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Background, licensing, education, expertise..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('staff.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-gold px-4 fw-bold">
                        Register Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
