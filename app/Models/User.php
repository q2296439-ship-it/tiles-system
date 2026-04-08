<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Branch;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // =====================
    // FILLABLE
    // =====================
    protected $fillable = [
        'name',
        'username', // 🔥 REQUIRED (dahil sa DB mo)
        'email',
        'password',
        'role',
        'branch_id',
    ];

    // =====================
    // HIDDEN
    // =====================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =====================
    // CASTS
    // =====================
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // =====================
    // RELATIONSHIP
    // =====================
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}