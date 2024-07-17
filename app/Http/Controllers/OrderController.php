<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;

class OrderController extends Controller
{
    private $service;

    public function __construct(OrderService $service)
    {
        $this->middleware('auth:api');
        $this->middleware('check.token');
        $this->service = $service;
    }

    public function clone(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $location = $request->input('location');

        $data = $this->service->clone($startDate, $endDate, $location);

        return response()->json([
            'status' => 'success',
            'message' => 'Order returned successfully',
            'data' => $data
        ]);
    }
}
