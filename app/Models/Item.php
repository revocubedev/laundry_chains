<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    //CREATE AN ITEM ID ON THE TRANSACTIONS TABLE
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function itemHistory()
    {
        return $this->hasMany(ItemHistory::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product_option()
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $fillable = [
        'tagid',
        'product_id',
        'product_option_id',
        'customer_id',
        'brand',
        'description',
        'notes'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
