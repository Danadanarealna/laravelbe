<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; // Ensure HasMany is imported

class User extends Authenticatable // This model represents UMKM Users
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', // UMKM Owner's name or general username
        'email',
        'password',
        'umkm_name',
        'contact', // Standardized from umkm_contact
        'is_investable', // New field
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array // Using the method for casts (Laravel 9+)
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_investable' => 'boolean', // Cast to boolean
        ];
    }

    public function transactions(): HasMany // Added return type
    {
        return $this->hasMany(Transaction::class);
    }

    public function investmentsReceived(): HasMany // Added return type
    {
        return $this->hasMany(Investment::class, 'umkm_id');
    }

    public function appointmentsReceived(): HasMany // Added return type
    {
        return $this->hasMany(Appointment::class, 'umkm_id');
    }
}
