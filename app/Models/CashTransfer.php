<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransfer extends Model
{
    protected $table = 'cash_transfers';

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'transfer_date',
        'amount',
        'notes',
        'status',
        'created_by',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}