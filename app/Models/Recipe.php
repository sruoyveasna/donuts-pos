<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = ['menu_item_id','menu_item_variant_id','ingredient_id','quantity'];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class)->withTrashed();
    }

    public function variant()
    {
        return $this->belongsTo(MenuItemVariant::class, 'menu_item_variant_id')->withTrashed();
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
