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
        return (float) $this->invoices()->where('payment_status', 'paid')->sum('total_amount');
    }

    public function getCompletedVisitsCountAttribute(): int
    {
        return $this->appointments()->where('status', 'completed')->count();
    }
}
