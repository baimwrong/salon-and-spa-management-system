@extends('layouts.app')

@section('title', 'Reschedule Booking #' . $appointment->appointment_number)
@section('page_title', 'Reschedule & Edit Appointment')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card aura-card">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Modify Appointment #{{ $appointment->appointment_number }}</h6>
                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Booking
                </a>
            </div>

            <form method="POST" action="{{ route('appointments.update', $appointment) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Customer Selection -->
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Client Profile <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ (old('customer_id', $appointment->customer_id) == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->phone }}) - Points: {{ $c->loyalty_points }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <span class="badge bg-light text-dark border p-2 w-100 text-center">
                                Booking Number: <strong>{{ $appointment->appointment_number }}</strong>
                            </span>
                        </div>

                        <!-- Date & Time -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', date('H:i', strtotime($appointment->start_time))) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Booking Origin</label>
                            <select name="booking_type" class="form-select">
                                <option value="phone" {{ old('booking_type', $appointment->booking_type) === 'phone' ? 'selected' : '' }}>Phone Reservation</option>
                                <option value="walk_in" {{ old('booking_type', $appointment->booking_type) === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                                <option value="online" {{ old('booking_type', $appointment->booking_type) === 'online' ? 'selected' : '' }}>Online Website</option>
                            </select>
                        </div>

                        <!-- Therapist & Room -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Primary Therapist / Stylist</label>
                            <select name="therapist_id" class="form-select">
                                <option value="">-- Any Available Staff Member --</option>
                                @foreach($therapists as $t)
                                    <option value="{{ $t->id }}" {{ old('therapist_id', $appointment->therapist_id) == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->specialization ?? 'Stylist' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Assigned Suite / Treatment Station</label>
                            <select name="room_id" class="form-select">
                                <option value="">-- Auto-Assign Station --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}" {{ old('room_id', $appointment->room_id) == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }} ({{ ucwords(str_replace('_', ' ', $r->type)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Services Selection -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Select Treatments & Services <span class="text-danger">*</span></label>
                            <div class="border rounded-3 p-3 bg-light bg-opacity-50" style="max-height: 280px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($services as $svc)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check p-2 bg-white rounded border">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="service_ids[]" value="{{ $svc->id }}" id="svc_{{ $svc->id }}" {{ (is_array(old('service_ids')) ? in_array($svc->id, old('service_ids')) : in_array($svc->id, $selectedServiceIds)) ? 'checked' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer" for="svc_{{ $svc->id }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-semibold small">{{ $svc->name }}</span>
                                                        <span class="fw-bold text-dark small">${{ number_format($svc->price, 2) }}</span>
                                                    </div>
                                                    <div class="text-muted small" style="font-size: 0.72rem;">
                                                        {{ $svc->category->name }} &bull; {{ $svc->duration_minutes }} min (+{{ $svc->buffer_time_minutes }}m buffer)
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('service_ids')
                                <div class="text-danger small mt-1">Please select at least one service.</div>
                            @enderror
                        </div>

                        <!-- Special Requests & Notes -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Client Special Requests or Preferences</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Skin sensitivity, preferred beverage, extra neck support...">{{ old('notes', $appointment->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-aura-gold px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Save & Reschedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
