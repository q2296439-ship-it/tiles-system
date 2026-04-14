<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'qty',
        'unit',
        'description',
        'unit_price',
        'amount',
    ];

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}