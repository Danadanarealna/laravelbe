<?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable; // Important: Use Authenticatable
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;
    
    class Investor extends Authenticatable // Extend Authenticatable for Sanctum
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
         * Get the attributes that should be cast.
         *
         * @return array<string, string>
         */
        protected function casts(): array
        {
            return [
                'email_verified_at' => 'datetime',
                'password' => 'hashed', // Ensure passwords are hashed
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
    }
    