<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_no',
        'receipt_no',
        'return_date',
        'customer_name',
        'reason',
        'total_amount',
        'branch_id',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}