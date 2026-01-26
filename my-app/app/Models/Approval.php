<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    //
        protected $fillable = [
        'request_id',
        'user_id',
        'action',
        'remarks'
    ];
}
