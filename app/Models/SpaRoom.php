<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpaRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'name',
        'type',
        'capacity',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'room_id');
    }
}
