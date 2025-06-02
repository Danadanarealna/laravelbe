<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasFactory;

    const STATUS_PENDING_VERIFICATION = 'pending_verification';
    const STATUS_VERIFIED_INCOME_RECORDED = 'verified_income_recorded';
    const STATUS_REPAID_BY_UMKM = 'repaid_by_umkm';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'date',
        'deadline',
        'amount',
        'status',
        'notes',
        'related_transaction_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'deadline' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING_VERIFICATION,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }
}
