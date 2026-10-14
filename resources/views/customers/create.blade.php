@extends('layouts.app')

@section('title', 'Add New Client Profile')
@section('page_title', 'Create Client Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus text-warning me-2"></i>New Client Information</h6>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Personal Info -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Camilla Thorne">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="+1 (555) 000-0000">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="client@example.com">
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Unspecified</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Residential Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Street, City, State, ZIP">
                        </div>

                        <!-- Diagnostic Profile -->
                        <div class="col-12">
                            <div class="border-top pt-3 mt-2">
                                <h6 class="fw-bold small text-uppercase text-muted"><i class="bi bi-heart-pulse me-1"></i>Beauty & Wellness Profile</h6>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Skin Type</label>
                            <select name="skin_type" class="form-select">
                                <option value="">Not evaluated</option>
                                <option value="Normal" {{ old('skin_type') === 'Normal' ? 'selected' : '' }}>Normal</option>
                                <option value="Dry" {{ old('skin_type') === 'Dry' ? 'selected' : '' }}>Dry</option>
                                <option value="Oily" {{ old('skin_type') === 'Oily' ? 'selected' : '' }}>Oily</option>
                                <option value="Combination" {{ old('skin_type') === 'Combination' ? 'selected' : '' }}>Combination</option>
                                <option value="Sensitive" {{ old('skin_type') === 'Sensitive' ? 'selected' : '' }}>Sensitive</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Hair Texture / Type</label>
                            <select name="hair_type" class="form-select">
                                <option value="">Not evaluated</option>
                                <option value="Straight" {{ old('hair_type') === 'Straight' ? 'selected' : '' }}>Straight</option>
                                <option value="Wavy" {{ old('hair_type') === 'Wavy' ? 'selected' : '' }}>Wavy</option>
                                <option value="Curly" {{ old('hair_type') === 'Curly' ? 'selected' : '' }}>Curly</option>
                                <option value="Coily" {{ old('hair_type') === 'Coily' ? 'selected' : '' }}>Coily</option>
                                <option value="Color-Treated" {{ old('hair_type') === 'Color-Treated' ? 'selected' : '' }}>Color-Treated / Bleached</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Preferred Therapist</label>
                            <select name="preferred_therapist_id" class="form-select">
                                <option value="">No preference</option>
                                @foreach($therapists as $t)
                                    <option value="{{ $t->id }}" {{ old('preferred_therapist_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->specialization ?? 'Stylist' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-danger"><i class="bi bi-shield-exclamation me-1"></i>Allergies / Medical Contraindications</label>
                            <input type="text" name="allergies" class="form-control border-danger border-opacity-50" value="{{ old('allergies') }}" placeholder="e.g. Nut oils, eucalyptus, latex, lavender, fragrance allergies...">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Profile Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="vip" {{ old('status') === 'vip' ? 'selected' : '' }}>VIP Client</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Private Client Notes & Preferences</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Beverage preferences, conversation preferences, pressure intensity, scalp sensitivities..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-gold px-4 fw-bold">
                        Save Client Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
