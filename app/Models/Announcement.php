<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'created_by',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}