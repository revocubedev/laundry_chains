<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function switchUser(Request $request)
    {
        $request->validate([
            'staff_code' => 'required'
        ]);

        $response = $this->service->switchUser($request->all());

        return response()->json($response);
    }

    public function create(CreateStaffRequest $request, $tenant)
    {
        Log::info([
            "route" => $request->path(),
            "tenant" => $tenant
        ]);
        $response = $this->service->create($request->validated(), $tenant);

        return response()->json($response);
    }
}
