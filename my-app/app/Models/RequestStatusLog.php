<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatusLog extends Model
{
    //
    protected $fillable = [
        'request_id',   // ✅ REQUIRED
        'status',
        'updated_by',
    ];

    // ✅ Recommended relationships
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
