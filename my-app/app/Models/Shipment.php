<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    //
       protected $fillable = [
        'request_id',
        'batch_number',
        'requestor_id',
        'shipped_date',
        'received_date',
        'status'
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
