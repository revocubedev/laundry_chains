<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'address',
        'phone_number',
        'route_id',
        'store_code'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function movementList()
    {
        return $this->hasMany(MovementList::class);
    }
    public function route()
    {
        return $this->belongsTo(Routes::class);
    }
}
