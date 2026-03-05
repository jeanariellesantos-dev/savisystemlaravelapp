<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealership extends Model
{
    //
    protected $fillable = [
        'dealership_name',
        'location',
        'is_active'
    ];

}
