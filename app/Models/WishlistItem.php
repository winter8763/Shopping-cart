<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $table = 'wishlist_items';

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'quantity',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class, 'wishlist_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
