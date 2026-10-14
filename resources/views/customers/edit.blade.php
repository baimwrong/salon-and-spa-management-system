@extends('layouts.app')

@section('title', 'Edit Client: ' . $customer->name)
@section('page_title', 'Edit Client Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editing: {{ $customer->name }} ({{ $customer->customer_code }})</h6>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Unspecified</option>
                                <option value="female" {{ old('gender', $customer->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="male" {{ old('gender', $customer->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="other" {{ old('gender', $customer->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($customer->date_of_birth)->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Residential Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
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
                                @foreach(['Normal', 'Dry', 'Oily', 'Combination', 'Sensitive'] as $st)
                                    <option value="{{ $st }}" {{ old('skin_type', $customer->skin_type) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Hair Texture / Type</label>
                            <select name="hair_type" class="form-select">
                                <option value="">Not evaluated</option>
                                @foreach(['Straight', 'Wavy', 'Curly', 'Coily', 'Color-Treated'] as $ht)
                                    <option value="{{ $ht }}" {{ old('hair_type', $customer->hair_type) === $ht ? 'selected' : '' }}>{{ $ht }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Preferred Therapist</label>
                            <select name="preferred_therapist_id" class="form-select">
                                <option value="">No preference</option>
                                @foreach($therapists as $t)
                                    <option value="{{ $t->id }}" {{ old('preferred_therapist_id', $customer->preferred_therapist_id) == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->specialization ?? 'Stylist' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-danger"><i class="bi bi-shield-exclamation me-1"></i>Allergies / Contraindications</label>
                            <input type="text" name="allergies" class="form-control border-danger border-opacity-50" value="{{ old('allergies', $customer->allergies) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Profile Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="vip" {{ old('status', $customer->status) === 'vip' ? 'selected' : '' }}>VIP Client</option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status', $customer->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Loyalty Points Balance</label>
                            <input type="number" min="0" name="loyalty_points" class="form-control" value="{{ old('loyalty_points', $customer->loyalty_points) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Private Client Notes & Preferences</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-primary px-4 fw-bold">
                        Update Client Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
