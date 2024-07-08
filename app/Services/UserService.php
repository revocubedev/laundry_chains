<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Exports\UserExport;
use App\Models\Tenant;
use App\Services\Helpers\MailService;
use Excel;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    private $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function switchUser($data)
    {
        $staffCode = $data['staff_code'];

        $user = User::with(['department', 'location'])
            ->where('staff_code', $staffCode)
            ->first();
        if (!$user) {
            throw new NotFoundException('User not found');
        };

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    public function create($data, $tenant)
    {
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

        $role = Role::find($data['role_id']);
        $password = $data['password'];

        $data['password'] = bcrypt($password);
        $data['role'] = $role->name;
        $data['staff_code'] = $code;

        $user = User::create($data);

        $currentTenant = Tenant::find($tenant);

        $this->mailService->sendStaffAddEmail([
            'to' => $data['email'],
            'content' => [
                'fullName' => $data['full_name'],
                'email' => $data['email'],
                'password' => $password,
                'staff_code' => $code,
                'companyName' => $currentTenant->organisation_name,
                'url' => $currentTenant->organisation_url
            ]
        ]);

        return $user;
    }
}
