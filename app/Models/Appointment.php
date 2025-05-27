<?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class Appointment extends Model
    {
        use HasFactory;
    
        protected $fillable = [
            'investor_id',
            'umkm_id',
            'investment_id',
            'appointment_details',
            'appointment_time',
            'status',
            'contact_method',
            'contact_payload',
        ];
    
        protected $casts = [
            'appointment_time' => 'datetime',
        ];
    
        public function investor()
        {
            return $this->belongsTo(Investor::class);
        }
    
        public function umkm()
        {
            return $this->belongsTo(User::class, 'umkm_id');
        }
    
        public function investment()
        {
            return $this->belongsTo(Investment::class);
        }
    }
    