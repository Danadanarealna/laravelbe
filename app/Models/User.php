<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'umkm_name',
        'contact',
        'is_investable',
        'umkm_description',
        'umkm_profile_image_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['umkm_profile_image_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_investable' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function investmentsReceived(): HasMany
    {
        return $this->hasMany(Investment::class, 'umkm_id');
    }

    public function appointmentsReceived(): HasMany
    {
        return $this->hasMany(Appointment::class, 'umkm_id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function getUmkmProfileImageUrlAttribute(): ?string
    {
        if ($this->umkm_profile_image_path) {
            return Storage::disk('public')->url($this->umkm_profile_image_path);
        }
        return null;
    }
}
