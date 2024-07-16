<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    //CREATE A CUSTOMER ID IN ORDER TABLE
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    //CREATE A CUSTOMER ID IN PRE_ORDER TABLE
    public function preOrders()
    {
        return $this->hasMany(PreOrder::class);
    }

    //CREATE A CUSTOMER ID IN ITEMS TABLE
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    protected $fillable = [
        'uuid',
        'full_name',
        'email',
        'phone',
        'secondary_phone',
        'street_address',
        'city',
        'gender',
        'birthday',
        'marketing_opt_in',
        'shirt_preference',
        'trouser_preference',
        'starch',
        'notes',
        'private_notes',
        'default_payment',
        'signed_up_date',
        'discount',
        'wallet',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
