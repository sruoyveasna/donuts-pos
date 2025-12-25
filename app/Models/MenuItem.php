<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'image',
        'description',
        'is_active',
        'discount_type',
        'discount_value',
        'discount_starts_at',
        'discount_ends_at',
    ];

    // Define relationships here, for example:
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function variants()
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    // Add scopes if needed, e.g. visible
    public function scopeVisible($query)
    {
        return $query->where('is_active', true);
    }
}
