<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTenantRequest;
use App\Services\Auth\TenantAuthService;

use Illuminate\Http\Request;

class TenantAuthController extends Controller
{
    private $service;

    public function __construct(TenantAuthService $service)
    {
        $this->service = $service;
    }

    public function registerTenant(RegisterTenantRequest $request)
    {
        $this->service->registerTenant($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation registered successfully'
        ], 201);
    }
}
