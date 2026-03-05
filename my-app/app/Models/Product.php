<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'product_name', 'description', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'product_units')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}

