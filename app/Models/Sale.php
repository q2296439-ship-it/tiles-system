<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'total_amount'
    ];

    // 🔥 RELATION: BRANCH
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    // 🔥 RELATION: USER (cashier)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // 🔥 RELATION: SALE ITEMS (IMPORTANT 🔥)
    public function items()
    {
        return $this->hasMany(\App\Models\SaleItem::class);
    }
}