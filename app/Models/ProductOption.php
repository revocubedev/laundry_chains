<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'option_name',
        'price',
        'expressPrice',
        'cost_price',
        'pieces'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
