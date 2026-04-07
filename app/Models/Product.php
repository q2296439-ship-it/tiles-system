<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category',
        'size',
        'color',
        'price',
        'stock',
        'low_stock_threshold',
        'branch_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}