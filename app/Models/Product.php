<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function product_group()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function product_option()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function item()
    {
        return $this->hasMany(Item::class);
    }

    public function itemHistories()
    {
        return $this->hasMany(ItemHistory::class);
    }

    protected $fillable = [
        'name',
        'product_group_id',
        'avatar'
    ];
}
