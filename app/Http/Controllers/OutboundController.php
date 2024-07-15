<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OutboundService;

class OutboundController extends Controller
{
    private $service;

    public function __construct(OutboundService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function handleOrderItems($item_id)
    {
        $message = $this->service->handleOrderItems($item_id);

        return response()->json([
            "status" => "success",
            "message" => $message
        ]);
    }
}
