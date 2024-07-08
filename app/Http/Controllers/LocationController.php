<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use Illuminate\Http\Request;

use App\Services\LocationService;

class LocationController extends Controller
{
    protected $service;

    public function __construct(LocationService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->index();

        return response()->json([
            'status' => 'success',
            'message' => 'Locations retrieved successfully',
            'data' => $data
        ]);
    }

    public function create(CreateLocationRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Location created successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateLocationRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully',
            'data' => $data
        ]);
    }

    public function show($uuid)
    {
        $data = $this->service->show($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Location retrieved successfully',
            'data' => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Location deleted successfully'
        ]);
    }
}
