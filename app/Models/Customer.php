<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'skin_type',
        'hair_type',
        'allergies',
        'preferred_therapist_id',
        'loyalty_points',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'loyalty_points' => 'integer',
        ];
    }

    public function preferredTherapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preferred_therapist_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getTotalSpendAttribute(): float
    {
        if (array_key_exists('total_spend', $this->attributes)) {
            return (float) ($this->attributes['total_spend'] ?? 0);
        }
        return (float) $this->invoices()->where('payment_status', 'paid')->sum('total_amount');
    }

    public function getCompletedVisitsCountAttribute(): int
    {
        if (array_key_exists('completed_visits_count', $this->attributes)) {
            return (int) ($this->attributes['completed_visits_count'] ?? 0);
        }
        return $this->appointments()->where('status', 'completed')->count();
    }
}
