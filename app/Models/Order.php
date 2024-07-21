<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'location_id',
        'bill',
        'dateTimeIn',
        'dateTimeOut',
        'note',
        'status',
        'discount',
        'itemsCount',
        'isExpress',
        'is_paid',
        'paidAmount',
        'staff_id',
        'paymentType',
        'delivery_id',
        'vat',
        'revenue',
        'discount_percentage',
        'extra_discount_value',
        'extra_discount_id',
        "extra_discount_percentage",
        "summary",
        "serial_number",
        'pre_order_code',
        'extra_discount_id',
    ];

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
}
