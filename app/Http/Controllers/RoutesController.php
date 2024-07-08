<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRouteRequest;
use App\Http\Requests\UpdateRouteRequest;

use App\Services\RouteService;

class RoutesController extends Controller
{
    private $service;

    public function __construct(RouteService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->index();

        return response()->json([
            'status' => 'success',
            'message' => 'Routes fetched successfully',
            'data' => $data
        ]);
    }

    public function create(CreateRouteRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Route created successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateRouteRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Route updated successfully',
            'data' => $data
        ]);
    }

    public function show($uuid)
    {
        $data = $this->service->show($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Route fetched successfully',
            'data' => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Route deleted successfully'
        ]);
    }
}
