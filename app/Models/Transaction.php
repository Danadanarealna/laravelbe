<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type', 
        'payment_method',
        'status',
        'date',
        'user_sequence_id',
        'notes', 
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public const ALLOWED_PAYMENT_METHODS = [
        'Cash', 'Credit', 'Bank Transfer', 'E-Wallet', 'Other'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
