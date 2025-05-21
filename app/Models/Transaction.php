<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'amount',
        'type',
        'status',
        'date',
        'user_id'
    ];
    protected $dates = ['date'];
    protected $casts = [
        'date' => 'datetime',
        'amount' => 'float'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}   
