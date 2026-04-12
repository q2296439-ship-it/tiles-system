<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Branch;
use App\Models\User;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'branch_id',
        'from_branch_id',
        'type',
        'quantity',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'released_by',
        'received_by',
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

    // 🔥 REQUESTED BY (cashier)
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // 🔥 APPROVED BY (manager)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 🔥 RELEASED BY (sender manager)
    public function releaser()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    // 🔥 RECEIVED BY (cashier receiver)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}