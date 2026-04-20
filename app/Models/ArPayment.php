<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArPayment extends Model
{
    protected $table = 'ar_payments';

    protected $fillable = [
        'collection_id',
        'branch_id',
        'payment_date',
        'amount',
        'created_by',
    ];
}