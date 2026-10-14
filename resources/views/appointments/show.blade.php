@extends('layouts.app')

@section('title', 'Booking #' . $appointment->appointment_number)
@section('page_title', 'Appointment Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Schedule
            </a>

            <div class="d-flex gap-2">
                @if($appointment->status !== 'completed' && $appointment->status !== 'cancelled')
                    <a href="{{ route('pos.terminal', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm btn-aura-gold shadow-sm">
                        <i class="bi bi-cash-coin me-1"></i>Proceed to POS Checkout
                    </a>
                @endif
                @if($appointment->invoice)
                    <a href="{{ route('pos.invoices.show', $appointment->invoice) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-receipt me-1"></i>View Paid Invoice
                    </a>
                @endif
            </div>
        </div>

        <div class="card aura-card mb-4">
            <div class="aura-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $appointment->appointment_number }}</h5>
                    <div class="small text-muted">{{ $appointment->appointment_date->format('l, F j, Y') }} &bull; {{ date('h:i A', strtotime($appointment->start_time)) }} - {{ date('h:i A', strtotime($appointment->end_time)) }}</div>
                </div>
                <div>
                    <span class="badge {{ $appointment->status_badge_class }} text-uppercase px-3 py-2 fs-6">
                        {{ str_replace('_', ' ', $appointment->status) }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <!-- Client Profile Section -->
                    <div class="col-12 col-md-6 border-end">
                        <h6 class="text-uppercase small fw-bold text-muted mb-3"><i class="bi bi-person me-1"></i>Client Profile</h6>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold fs-5 text-dark" style="width: 50px; height: 50px;">
                                {{ substr($appointment->customer->name, 0, 1) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $appointment->customer->name }}</h6>
                                <div class="small text-muted">{{ $appointment->customer->phone }} &bull; {{ $appointment->customer->email ?? 'No email' }}</div>
                                <span class="badge bg-soft-gold text-dark mt-1">{{ $appointment->customer->loyalty_points }} Loyalty Points</span>
                            </div>
                        </div>

                        @if($appointment->customer->allergies)
                            <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <div><strong>Allergy Warning:</strong> {{ $appointment->customer->allergies }}</div>
                            </div>
                        @endif

                        <div class="small text-muted">
                            <div><strong>Skin Type:</strong> {{ $appointment->customer->skin_type ?? 'Not recorded' }}</div>
                            <div><strong>Hair Type:</strong> {{ $appointment->customer->hair_type ?? 'Not recorded' }}</div>
                        </div>
                    </div>

                    <!-- Booking & Facility Section -->
                    <div class="col-12 col-md-6">
                        <h6 class="text-uppercase small fw-bold text-muted mb-3"><i class="bi bi-geo-alt me-1"></i>Facility & Assignment</h6>
                        <div class="mb-3">
                            <div class="small text-muted">Assigned Specialist:</div>
                            <div class="fw-bold text-dark fs-6">{{ $appointment->therapist?->name ?? 'Unassigned' }}</div>
                            <div class="small text-muted">{{ $appointment->therapist?->specialization ?? 'Spa Therapist' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Treatment Room / Station:</div>
                            <div class="fw-bold text-dark">{{ $appointment->room?->name ?? 'Open Station' }}</div>
                            <div class="small text-muted">{{ $appointment->room ? ucwords(str_replace('_', ' ', $appointment->room->type)) : '' }}</div>
                        </div>

                        <div>
                            <div class="small text-muted">Booking Channel:</div>
                            <span class="badge bg-light text-dark border text-uppercase">{{ $appointment->booking_type }}</span>
                        </div>
                    </div>
                </div>

                <!-- Booked Services Table -->
                <h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="bi bi-scissors me-1"></i>Booked Treatments</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Service Name</th>
                                <th>Category</th>
                                <th class="text-center">Duration</th>
                                <th class="text-end">Service Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointment->items as $item)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $item->service->name }}</td>
                                    <td class="small text-muted">{{ $item->service->category->name }}</td>
                                    <td class="text-center small">{{ $item->duration_minutes }} minutes</td>
                                    <td class="text-end fw-bold">${{ number_format($item->price, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Estimated Total:</td>
                                <td class="text-end text-dark">${{ number_format($appointment->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($appointment->notes)
                    <div class="p-3 bg-light rounded-3 small text-muted mb-4">
                        <strong>Special Requests:</strong> {{ $appointment->notes }}
                    </div>
                @endif

                <!-- Status Updater Control -->
                <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                    <h6 class="fw-bold small text-uppercase mb-2">Update Booking Workflow Status:</h6>
                    <form method="POST" action="{{ route('appointments.status', $appointment) }}" class="d-flex flex-wrap gap-2 align-items-center">
                        @csrf
                        @if($appointment->status !== 'in_service')
                            <button type="submit" name="status" value="in_service" class="btn btn-sm btn-primary">
                                <i class="bi bi-play-circle me-1"></i>Start Treatment (In-Service)
                            </button>
                        @endif

                        @if($appointment->status !== 'completed')
                            <button type="submit" name="status" value="completed" class="btn btn-sm btn-success">
                                <i class="bi bi-check2-circle me-1"></i>Mark Completed
                            </button>
                        @endif

                        @if($appointment->status !== 'cancelled')
                            <button type="submit" name="status" value="cancelled" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this appointment?')">
                                <i class="bi bi-x-circle me-1"></i>Cancel Booking
                            </button>
                        @endif

                        @if($appointment->status !== 'no_show')
                            <button type="submit" name="status" value="no_show" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-person-x me-1"></i>Mark No-Show
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
