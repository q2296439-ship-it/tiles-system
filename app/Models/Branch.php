<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address' // 🔥 FIX: idagdag ito
    ];

    // 🔥 relationship: isang branch maraming products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // 🔥 relationship: isang branch maraming users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}