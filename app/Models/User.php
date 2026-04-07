<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Branch; // 🔥 ADD THIS

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 🔥 IMPORTANT FIELDS
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 🔥 FIX: RELATIONSHIP
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}