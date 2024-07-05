<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function itemHistory()
    {
        return $this->hasMany(ItemHistory::class);
    }

    protected $fillable = [
        'uuid',
        'name',
        'scan_in',
        'scan_out',
        'action_options',
        'default_action',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
