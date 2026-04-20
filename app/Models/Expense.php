<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'branch_id',
        'category_id',
        'expense_date',
        'description',
        'amount',
        'payment_method',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}