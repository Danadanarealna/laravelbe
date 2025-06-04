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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'umkm_name',
        'contact',
        'is_investable',
        'umkm_description',
        'umkm_profile_image_path',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = ['umkm_profile_image_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_investable' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the investments received by the UMKM user.
     */
    public function investmentsReceived(): HasMany
    {
        return $this->hasMany(Investment::class, 'umkm_id');
    }

    /**
     * Get the appointments received by the UMKM user.
     */
    public function appointmentsReceived(): HasMany
    {
        return $this->hasMany(Appointment::class, 'umkm_id');
    }

    /**
     * Get the debts for the user.
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * Get the full URL for the UMKM profile image (using ImageController route).
     *
     * @return string|null
     */
    public function getUmkmProfileImageUrlAttribute(): ?string
    {
        if (empty($this->umkm_profile_image_path)) {
            return null;
        }

        // Return the URL that goes through our ImageController
        $cleanPath = ltrim($this->umkm_profile_image_path, '/');
        return url('/api/images/' . $cleanPath);
    }

    /**
     * Check if UMKM has a profile image.
     *
     * @return bool
     */
    public function hasUmkmProfileImage(): bool
    {
        return !empty($this->umkm_profile_image_path);
    }

    /**
     * Get UMKM's initials for avatar fallback.
     *
     * @return string
     */
    public function getUmkmInitials(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }
}