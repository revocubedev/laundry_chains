<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\Request;

use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    private $service;

    public function __construct(DepartmentService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->index();

        return response()->json([
            'status' => 'success',
            'message' => 'Department retrieved successfully',
            'data' => $data
        ]);
    }

    public function create(CreateDepartmentRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Department created successfully',
            'data' => $data
        ]);
    }

    public function show($uuid)
    {
        $data = $this->service->show($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Department retrieved successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateDepartmentRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Department updated successfully',
            'data' => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully'
        ]);
    }
}
