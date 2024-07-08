<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    protected $fillable = [
        'id',
        'email',
        'full_name',
        'organisation_name',
        'organisation_email',
        'organisation_url',
        'tenancy_db_username',
        'tenancy_db_password',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'email',
            'full_name',
            'organisation_name',
            'organisation_email',
            'organisation_url',
            'tenancy_db_username',
            'tenancy_db_password',
        ];
    }
}
