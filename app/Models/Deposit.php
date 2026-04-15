<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_date',
        'branch_id',
        'user_id',

        'expected_amount',
        'actual_amount',
        'variance',

        'status',
        'reopened_by',
        'reopened_at',
        'reopen_reason',

        'denom_1000',
        'denom_500',
        'denom_200',
        'denom_100',
        'denom_50',
        'denom_20',

        'coin_10',
        'coin_5',
        'coin_1',

        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reopenedBy()
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}