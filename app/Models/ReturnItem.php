<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_id',
        'qty',
        'unit',
        'description',
        'unit_price',
        'amount',
    ];

    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }
}