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
}
