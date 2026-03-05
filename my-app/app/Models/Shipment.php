<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    //
       protected $fillable = [
        'request_id',
        'batch_number',
        'shipped_by',
        'shipped_date',
        'received_date',
        'tracking_link',
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
