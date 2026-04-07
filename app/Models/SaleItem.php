<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price'
    ];

    // 🔥 RELATION TO SALE
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // 🔥 RELATION TO PRODUCT
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}