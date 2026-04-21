<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefectiveStock extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'quantity',
        'reason',
        'user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}