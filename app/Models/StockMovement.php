<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'from_branch_id', // 🔥 ADD
        'type',
        'quantity',
        'reason',
    ];

    // 🔥 PRODUCT
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔥 TO BRANCH (destination)
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // 🔥 FROM BRANCH (source)
    public function from_branch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }
}