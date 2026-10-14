@extends('layouts.app')

@section('title', 'Edit Staff: ' . $user->name)
@section('page_title', 'Edit Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editing: {{ $user->name }}</h6>
                <a href="{{ route('staff.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('staff.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">System Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="therapist" {{ old('role', $user->role) === 'therapist' ? 'selected' : '' }}>Stylist / Therapist</option>
                                <option value="receptionist" {{ old('role', $user->role) === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Salon Operations Manager</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>System Administrator</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Phone Contact</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Specialization / Title</label>
                            <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $user->specialization) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Service Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.50" min="0" max="100" name="commission_rate" class="form-control" value="{{ old('commission_rate', $user->commission_rate) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Employment Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active Staff</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="on_leave" {{ old('status', $user->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Bio / Professional Certifications</label>
                            <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('staff.show', $user) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-primary px-4 fw-bold">
                        Update Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
