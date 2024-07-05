<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function departments()
    {
        return $this->belongsTo(Department::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }

    protected $fillable = [
        'item_id',
        'order_id',
        'transaction_id',
        'department_id',
        'staff_id',
        'stage',
        'extra_info'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
