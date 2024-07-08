<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\User;
use App\Models\Role;
use App\Exports\UserExport;
use App\Models\Tenant;
use App\Services\Helpers\MailService;
use Maatwebsite\Excel\Facades\Excel;
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

    public function index($search = null, $per_page = 50, $department_id = null)
    {
        $users = User::join('departments', 'departments.id', '=', 'users.department_id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->join('locations', 'locations.id', '=', 'users.location_id')
            ->when($search, function ($query) use ($search) {
                return $query->where('users.fullName', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhere('users.phoneNumber', 'like', '%' . $search . '%');
            })
            ->when($department_id, function ($query) use ($department_id) {
                return $query->where('users.department_id', $department_id);
            })
            ->select(
                'users.id',
                'users.uuid',
                'users.fullName',
                'users.email',
                'users.phoneNumber',
                'users.permissions',
                "users.staff_code",
                'roles.name as user_role',
                'roles.id as role_id',
                'users.department_id',
                'departments.name as department_name',
                'locations.id as location_id',
                'locations.locationName'
            )
            ->paginate($per_page);

        return $users;
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
                'fullName' => $data['fullName'],
                'email' => $data['email'],
                'password' => $password,
                'staff_code' => $code,
                'companyName' => $currentTenant->organisation_name,
                'url' => $currentTenant->organisation_url
            ]
        ]);

        return $user;
    }

    public function details($uuid)
    {
        $user = User::join('departments', 'departments.id', '=', 'users.department_id')
            ->join('locations', 'locations.id', '=', 'users.location_id')
            ->where('users.uuid', $uuid)
            ->select('users.id', 'users.uuid', 'users.fullName', 'users.email', 'users.phoneNumber', 'users.location_id', 'departments.id as department_id', 'departments.name as department_name')
            ->get();
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return $user;
    }

    public function edit($data, $uuid)
    {
        $role = Role::find($data['role_id']);
        $user = User::where('uuid', $uuid)->first();
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if (isset($data['role_id'])) {
            $data['role'] = $role->name;
        }

        $user->update($data);

        return $user;
    }

    public function delete($uuid)
    {
        $user = User::where('uuid', $uuid)->first();
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        $user->delete();
    }

    public function export_users()
    {
        $curr_date = date('Y-m-d');
        $exportedUsers = new UserExport();

        return Excel::download($exportedUsers, 'users-' . $curr_date . '.xlsx');
    }
}
