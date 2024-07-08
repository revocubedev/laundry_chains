<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Department;

class DepartmentService
{
    public function index()
    {
        return Department::all();
    }

    public function create($data)
    {
        return Department::create($data);
    }

    public function edit($data, $uuid)
    {
        $department = Department::where('uuid', $uuid)->first();
        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        $department->update($data);

        return $department;
    }

    public function show($uuid)
    {
        $department = Department::where('uuid', $uuid)->first();
        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        return $department;
    }

    public function delete($uuid)
    {
        $department = Department::where('uuid', $uuid)->first();
        if (!$department) {
            throw new NotFoundException('Department not found');
        }

        $department->delete();
    }
}
