<?php

namespace App\Services\Auth;

use App\Models\Tenant;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Services\Helpers\MailService;

class TenantAuthService
{
    private $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function registerTenant(array $data): void
    {
        $emailExists = Tenant::where('email', $data['email'])
            ->first();
        if ($emailExists) {
            throw new BadRequestException('Email already exists');
        }

        $tenantExists = Tenant::where('organisation_name', str_replace(' ', '_', strtolower($data['organisation_name'])))
            ->first();
        if ($tenantExists) {
            throw new BadRequestException('Organisation already exists');
        }

        $orgName = $data['organisation_name'];
        $data['organisation_name'] = str_replace(' ', '_', strtolower($orgName));
        $data['organisation_url'] = env('FRONTEND_URL') . '/' . $data['organisation_name'];
        $data['tenancy_db_name'] = "tenant_" . $data['organisation_name'];
        $data['tenancy_db_username'] = env('DB_USERNAME');
        $data['tenancy_db_password'] = env('DB_PASSWORD');

        $tenantData = [
            'email' => $data['email'],
            'full_name' => $data['full_name'],
            'organisation_name' => str_replace(' ', '_', strtolower($data['organisation_name'])),
            'organisation_email' => $data['organisation_email'],
            'organisation_url' => env('FRONTEND_URL') . '/' . $data['organisation_name'],
            'tenancy_db_name' => "tenant_" . $data['organisation_name'],
            'tenancy_db_username' => env('DB_USERNAME'),
            'tenancy_db_password' => env('DB_PASSWORD'),
        ];

        $tenant = Tenant::create($tenantData);

        $this->mailService->sendWelcomeEmail([
            'to' => $data['email'],
            'content' => [
                'companyName' => $orgName,
                'url' => env('FRONTEND_URL') . '/' . $data['organisation_name'] . '/login',
            ]
        ]);

        $uppercaseLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        $code = '';

        // Generate 2 random uppercase letters
        for ($i = 0; $i < 2; $i++) {
            $code .= $uppercaseLetters[rand(0, strlen($uppercaseLetters) - 1)];
        }

        // Generate 2 random digits
        for ($i = 0; $i < 2; $i++) {
            $code .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        $tenant->run(function () use ($tenant, $code, $data) {
            User::create([
                'full_name' => $tenant->full_name,
                'email' => $tenant->email,
                'password' => bcrypt($data['password']),
                'role' => 'admin',
                'role_id' => 1,
                'permissions' => ["create-user"],
                'staff_code' => $code,
            ]);
        });
    }
}
