<?php

namespace App\Exports;

use App\Models\User;

use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Session;

class UserExport implements FromArray, WithHeadings
{
    public function array():array
    {
        $users = User::join('departments', 'departments.id', '=', 'users.department_id')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.uuid', 'users.fullName', 'users.email', 'users.phoneNumber', 'roles.name as user_role', 'users.department_id', 'departments.name as department_name')
        ->get();

        $data = [];

        foreach ($users as $user) {
            $data[$user->id] = [
                'uuid' => $user->uuid,
                'fullName' => $user->fullName,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'userRole' => $user->user_role,
                'department' => $user->department_name
            ];
        }

        return $data;
    }



    public function headings():array 
    {
        $users = User::join('departments', 'departments.id', '=', 'users.department_id')
        ->join('roles', 'roles.id', '=', 'users.role_id')
        ->select('users.id', 'users.uuid', 'users.fullName', 'users.email', 'users.phoneNumber', 'roles.name as user_role', 'users.department_id', 'departments.name as department_name')
        ->get();

        $data = [];

        for($i=0; $i < count($users); $i++) {
            $data[$i] = [
                'uuid' => $user[$i]->uuid,
                'fullName' => $user[$i]->fullName,
                'email' => $user[$i]->email,
                'phoneNumber' => $user[$i]->phoneNumber,
                'userRole' => $user[$i]->user_role,
                'department' => $user[$i]->department_name
            ];
        }
        $array1 = data[0];
        return array_keys($array1);
    }
}
