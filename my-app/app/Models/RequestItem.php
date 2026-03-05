<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'product_id',
        'unit_id',
        'quantity',
        'starting_balance',
        'ending_balance'
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

        public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
