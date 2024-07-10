<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryOptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount'
    ];

    //ADD A DELIVERY ID IN THE ORDERS
    public function order()
    {
        return $this->hasMany(Order::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
