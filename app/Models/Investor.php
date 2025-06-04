<?php
    
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class Investor extends Authenticatable
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
        'profile_image_path', // Ensure this field is in your database table
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
    protected $appends = ['profile_image_url']; // This will call getProfileImageUrlAttribute

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
        ];
    }

    // An investor can make multiple investments
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    // An investor can have multiple appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the profile image URL, ensuring it uses the /api/images route.
     * This accessor is automatically called due to $appends.
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image_path) {
            return null;
        }

        // Ensure the path doesn't have leading slashes for URL generation
        $cleanPath = ltrim($this->profile_image_path, '/');
        
        // Construct the URL using the /api/images/ route
        return url('/api/images/' . $cleanPath);
    }

    /**
     * Get the API URL for the profile image (alternative explicit method if needed).
     * This is essentially the same as getProfileImageUrlAttribute now.
     *
     * @return string|null
     */
    public function getApiImageUrl(): ?string
    {
        return $this->getProfileImageUrlAttribute(); // Delegate to the accessor
    }

    /**
     * Check if investor has a profile image.
     *
     * @return bool
     */
    public function hasProfileImage(): bool
    {
        return !empty($this->profile_image_path);
    }

    /**
     * Get investor's initials for avatar fallback.
     *
     * @return string
     */
    public function getInitials(): string
    {
        if (empty($this->name)) {
            return 'I'; // Default initial if name is empty
        }
        return strtoupper(substr($this->name, 0, 1));
    }
}
