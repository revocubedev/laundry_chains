<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'total',
        'isPaid'
    ];

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
}
