<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'itemable_id',
        'itemable_type',
        'quantity'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function itemable()
    {
        return $this->morphTo();
    }
}
