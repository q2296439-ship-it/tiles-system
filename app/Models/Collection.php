<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'receipt_date',
        'customer_name',
        'address',
        'terms',

        'gross_amount',
        'discount_type',
        'discount_amount',
        'net_amount',

        'total_amount',
        'branch_id',
        'user_id',
        'status',
        'cancel_reason',
    ];

    protected $attributes = [
        'status' => 'saved',
        'gross_amount' => 0,
        'discount_amount' => 0,
        'net_amount' => 0,
    ];

    public function items()
    {
        return $this->hasMany(CollectionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}