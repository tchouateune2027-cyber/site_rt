<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'duration',
        'image',
        'is_active'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }
    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }
}
