<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;
use App\Models\Branch;

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

    // 🔗 Relationship: Product belongs to a branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // 🔥 IMPORTANT: para gumana yung withSum (branch stock)
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }
}