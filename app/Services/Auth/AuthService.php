<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;

class AuthService
{
    public function login($data)
    {
        $token = auth('api')->attempt($data);
        if (!$token) {
            throw new BadRequestException('Invalid email or password');
        };

        $user = User::with(['department', 'location'])
            ->where('users.email', '=', $data['email'])
            ->first();
        if (!$user) {
            throw new NotFoundException('User not found');
        };

        return [
            'token' => $token,
            'user' => $user
        ];
    }
}
