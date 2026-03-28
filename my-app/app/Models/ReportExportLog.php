<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExportLog extends Model
{
    protected $fillable = [
        'exported_by',
        'export_format',
        'text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}