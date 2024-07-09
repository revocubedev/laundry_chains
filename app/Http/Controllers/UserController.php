<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function switchUser(Request $request)
    {
        $request->validate([
            'staff_code' => 'required'
        ]);

        $data = $this->service->switchUser($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'User switched successfully',
            'data' => $data
        ]);
    }

    public function index(Request $request)
    {
        $data = $this->service->index(
            $request->query('search'),
            $request->query('per_page'),
            $request->query('departmentId')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Users fetched successfully',
            'data' => $data
        ]);
    }

    public function create(CreateStaffRequest $request)
    {
        $tenant = explode('/', $request->path())[0];
        $data = $this->service->create($request->validated(), $tenant);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $data
        ]);
    }

    public function details($uuid)
    {
        $data = $this->service->details($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'User details fetched successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateStaffRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    public function export_users()
    {
        $data = $this->service->export_users();

        return response()->json([
            'status' => 'success',
            'message' => 'Users exported successfully',
            'data' => $data
        ]);
    }
}
