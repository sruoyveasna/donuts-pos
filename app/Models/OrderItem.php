<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','menu_item_id','menu_item_variant_id',
        'quantity','price','subtotal','customizations','note',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'price'          => 'float',
        'subtotal'       => 'float',
        'customizations' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function menuItemVariant()
    {
        return $this->belongsTo(MenuItemVariant::class, 'menu_item_variant_id');
    }
}
