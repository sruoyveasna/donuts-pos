<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','method','status','transaction_id','confirmed_at',
        'currency','amount_khr','tendered_khr','change_khr','exchange_rate','meta',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'amount_khr'   => 'integer',
        'tendered_khr' => 'integer',
        'change_khr'   => 'integer',
        'exchange_rate'=> 'float',
        'meta'         => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope helper
    public function scopeConfirmed($q)
    {
        return $q->where('status', 'confirmed');
    }
}
