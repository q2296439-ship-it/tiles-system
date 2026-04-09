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
    // 🔥 AUTO HASH PASSWORD (FIX)
    // =====================
    public function setPasswordAttribute($value)
    {
        // kung hindi pa hashed, i-hash natin
        if (!\Illuminate\Support\Facades\Hash::needsRehash($value)) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = bcrypt($value);
        }
    }

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