<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Money are KHR integers; rates float; paid_at datetime
    protected $fillable = [
        'user_id','order_code','status','paid_at',
        'subtotal_khr','discount_khr','tax_khr','total_khr',
        'total_items','tax_rate','exchange_rate','discount_id',
    ];

    protected $casts = [
        'paid_at'       => 'datetime',
        'subtotal_khr'  => 'integer',
        'discount_khr'  => 'integer',
        'tax_khr'       => 'integer',
        'total_khr'     => 'integer',
        'total_items'   => 'integer',
        'tax_rate'      => 'float',
        'exchange_rate' => 'float',
    ];

    protected $appends = ['paid_khr','due_khr','is_paid'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function discount()
    {
        return $this->belongsTo(\App\Models\Discount::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Computed
    public function getPaidKhrAttribute(): int
    {
        return (int) $this->payments()->where('status','confirmed')->sum('amount_khr');
    }

    public function getDueKhrAttribute(): int
    {
        return max(0, (int)$this->total_khr - (int)$this->paid_khr);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->due_khr === 0;
    }
}
