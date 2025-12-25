<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','code','type','value',
        'min_subtotal_khr','max_discount_khr',
        'is_active','starts_at','ends_at',
        'usage_limit','used_count','meta',
    ];

    protected $casts = [
        'value'            => 'decimal:2',
        'min_subtotal_khr'  => 'integer',
        'max_discount_khr'  => 'integer',
        'is_active'         => 'boolean',
        'starts_at'         => 'datetime',
        'ends_at'           => 'datetime',
        'usage_limit'       => 'integer',
        'used_count'        => 'integer',
        'meta'              => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isUsable(int $subtotalKhr): bool
    {
        if (!$this->is_active) return false;

        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;

        if ($this->min_subtotal_khr !== null && $subtotalKhr < (int)$this->min_subtotal_khr) return false;

        if ($this->usage_limit !== null && (int)$this->used_count >= (int)$this->usage_limit) return false;

        return true;
    }

    public function computeDiscountKhr(int $subtotalKhr): int
    {
        if ($subtotalKhr <= 0) return 0;

        if ($this->type === 'percent') {
            $raw = (int) round($subtotalKhr * ((float)$this->value / 100));
        } else {
            $raw = (int) round((float)$this->value);
        }

        $raw = max(0, min($raw, $subtotalKhr));

        if ($this->max_discount_khr !== null) {
            $raw = min($raw, (int)$this->max_discount_khr);
        }

        return $raw;
    }
}
