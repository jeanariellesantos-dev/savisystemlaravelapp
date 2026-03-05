<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
    'user_id',
    'request_id',
    'title',
    'message',
    'is_read'
];

public function request()
{
    return $this->belongsTo(\App\Models\Request::class);
}
}
