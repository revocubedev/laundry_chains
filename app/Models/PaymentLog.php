<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentLog extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function staff()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'method_of_payment',
        'location_id',
        'purpose',
        'user_id'
    ];
}
