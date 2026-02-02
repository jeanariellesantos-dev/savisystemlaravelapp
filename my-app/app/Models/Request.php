<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Request extends Model
{

    protected $fillable = [ 'requestor_id', 'status'];

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class,'request_id');
    }

    public function requestStatusLog()
    {
        return $this->hasMany(RequestStatusLog::class);
    }
    
     protected static function booted()
    {
        static::creating(function ($model) {
            $model->request_id = 'REQ'
                . now()->format('YmdHis')
                . random_int(10, 99);
        });
    }

}
