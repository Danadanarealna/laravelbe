<?php
    // File: app/Models/Investment.php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Investment extends Model
    {
        use HasFactory;

        protected $fillable = [
            'investor_id',
            'umkm_id',
            'amount',
            'investment_date', // Correctly included
            'status',
        ];

        protected $casts = [
            'amount' => 'decimal:2',
            'investment_date' => 'datetime', // Correctly included
        ];

        public function investor(): BelongsTo
        {
            return $this->belongsTo(Investor::class);
        }

        public function umkm(): BelongsTo
        {
            return $this->belongsTo(User::class, 'umkm_id');
        }
    }
    