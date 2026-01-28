<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'quantity',
        'unit_of_measure'
    ];

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }
}
