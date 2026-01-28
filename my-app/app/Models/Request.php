<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Request extends Model
{

    protected $fillable = [ 'requestor_id','description', 'status'];

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
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
