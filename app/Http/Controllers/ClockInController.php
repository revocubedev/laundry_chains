<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClockInService;

class ClockInController extends Controller
{
    private $service;

    public function __construct(ClockInService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index()
    {
        $clockins = $this->service->index();

        return response()->json([
            'status' => 'success',
            'message' => 'Clockins retrieved successfully',
            'clockins' => $clockins
        ]);
    }

    public function clockin(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $this->service->clockIn($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'You have clocked in successfully'
        ]);
    }

    public function clockout(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $this->service->clockout($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'You have clocked out successfully'
        ]);
    }

    public function clockin_history($uuid)
    {
        $clockins = $this->service->clockin_history($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Clockins retrieved successfully',
            'clockins' => $clockins
        ]);
    }

    public function verify_clockin($uuid)
    {
        $clockin = $this->service->verify_clockin($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Clockin verified successfully',
            'clockin' => $clockin
        ]);
    }
}
