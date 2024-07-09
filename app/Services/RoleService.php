<?php

namespace App\Services;

use App\Models\Role;
use App\Exceptions\NotFoundException;

class RoleService
{
    public function create($data)
    {
        return Role::create($data);
    }

    public function getAllRoles()
    {
        return Role::all();
    }

    public function getRole($uuid)
    {
        $role = Role::where('uuid', $uuid)->first();
        if (!$role) {
            throw new NotFoundException('Role not found');
        };

        return $role;
    }

    public function edit($data, $uuid)
    {
        $role = Role::where('uuid', $uuid)->first();
        if (!$role) {
            throw new NotFoundException('Role not found');
        };

        $role->update($data);

        return $role;
    }

    public function delete($uuid)
    {
        $role = Role::where('uuid', $uuid)->first();
        if (!$role) {
            throw new NotFoundException('Role not found');
        };

        $role->delete();
    }
}
