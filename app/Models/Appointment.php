<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'customer_id',
        'therapist_id',
        'room_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'booking_type',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(SpaRoom::class, 'room_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'confirmed' => 'bg-info text-dark',
            'in_service' => 'bg-primary text-white',
            'completed' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
            'no_show' => 'bg-secondary text-white',
            default => 'bg-light text-dark',
        };
    }
}
