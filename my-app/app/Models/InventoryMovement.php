<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'dealership_id',
        'unit_id',
        'type',
        'quantity',
        'starting_balance',
        'ending_balance',
        'reference_type',
        'reference_id',
        'remarks',
        'created_by'
    ];
}
