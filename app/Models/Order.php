<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function outbounds()
    {
        return $this->hasMany(Outbound::class);
    }

    public function outbound_iteration()
    {
        return $this->hasMany(OutboundIteration::class);
    }

    public function orderInvoice()
    {
        return $this->hasMany(OrderInvoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function deletedOrder()
    {
        return $this->belongsTo(DeletedOrder::class);
    }

    public function deliveryOption()
    {
        return $this->belongsTo(DeliveryOptions::class);
    }

    public function preOrders()
    {
        return $this->belongsTo(PreOrder::class);
    }


    protected $fillable = [
        'uuid',
        'customer_id',
        'bill',
        'dateTimeIn',
        'dateTimeOut',
        'note',
        'location_id',
        'itemsCount',
        'pickUpCode',
        'status'
    ];
}
