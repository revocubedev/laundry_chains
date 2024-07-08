<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'scan_in',
        'scan_out',
        'action_options',
        'default_options',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function itemHistory()
    {
        return $this->hasMany(ItemHistory::class);
    }
}
