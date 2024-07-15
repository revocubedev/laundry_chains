<?php

namespace App\Http\Controllers;

use App\Http\Requests\BatchScanRequest;
use App\Http\Requests\CreateItemHistoryRequest;
use Illuminate\Http\Request;
use App\Services\ItemHistoryService;

class ItemHistoryController extends Controller
{
    private $service;

    public function __construct(ItemHistoryService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function create_history(CreateItemHistoryRequest $request)
    {
        $data = $this->service->create_history($request->validated());

        return response()->json([
            "status" => "success",
            "message" => "Item history created successfully",
            "data" => $data
        ]);
    }

    public function batchScan(BatchScanRequest $request)
    {
        $this->service->batchScan($request->validated());

        return response()->json([
            "status" => "success",
            "message" => "Batch scan completed",
        ]);
    }

    public function get_recent_history($transId)
    {
        $data = $this->service->get_recent_history($transId);

        return response()->json([
            "status" => "success",
            "message" => "Successfully retrieved recent history",
            "data" => $data
        ]);
    }

    public function get_all_history(Request $request)
    {
        $data = $this->service->get_all_history(
            $request->query('transaction_id'),
            $request->query('department_id'),
            $request->query('user_id')
        );

        return response()->json([
            "status" => "success",
            "message" => "Item history retrieved successfully",
            "data" => $data
        ]);
    }

    public function export($transId)
    {
        $data = $this->service->export($transId);

        return response()->json([
            "status" => "success",
            "message" => "Data successfully exported",
            "data" => $data
        ]);
    }
}
