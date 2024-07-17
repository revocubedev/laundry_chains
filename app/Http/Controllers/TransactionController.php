<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;

use App\Services\TransactionService;

class TransactionController extends Controller
{
    private $service;

    public function __construct(TransactionService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->index();

        return response()->json([
            "status" => "success",
            "message" => "Successfully retrieved transactions",
            "data" => $data
        ]);
    }

    public function view($uuid)
    {
        $data = $this->service->view($uuid);

        return response()->json([
            "status" => "success",
            "message" => "Successfully retrieved transaction",
            "data" => $data
        ]);
    }

    public function edit(UpdateTransactionRequest $request, $uuid)
    {
        $data = $this->service->edit($uuid, $request->all());

        return response()->json([
            "status" => "success",
            "message" => "Transaction updated successfully",
            "data" => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            "status" => "success",
            "message" => "Transaction deleted successfully"
        ]);
    }

    public function trackItem($tagId)
    {
        $data = $this->service->trackItem($tagId);

        return response()->json([
            "status" => "success",
            "message" => "Successfully retrieved transaction",
            "data" => $data
        ]);
    }

    public function ordersPerHour(Request $request)
    {
        $data = $this->service->ordersPerHour($request->location);

        return response()->json([
            "status" => "success",
            "message" => "Successfully retrieved transactions",
            "data" => $data
        ]);
    }

    public function ordersCompare(Request $request)
    {
        $previousPeriod = $request->query('previousPeriod');
        $lastMonth = $request->query('lastMonth');
        $lastQuarter = $request->query('lastQuarter');
        $lastYear = $request->query('lastYear');
        $startDate = $request->query('start_date_period_2');
        $endDate = $request->query('end_date_period_2');
        $limit = $request->query('limit');

        $data = $this->service->ordersCompare(
            $previousPeriod,
            $lastMonth,
            $lastQuarter,
            $lastYear,
            $startDate,
            $endDate,
            $limit
        );

        return response()->json([
            "status" => "success",
            "message" => "Metrics retrieved successfully",
            "data" => $data
        ]);
    }
}
