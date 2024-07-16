<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\MergeCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    private $service;

    public function __construct(CustomerService $service)
    {
        $this->middleware('auth:api');
        $this->middleware('check.token');
        $this->service = $service;
    }

    public function detail($uuid)
    {
        $data = $this->service->detail($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer returned successfully',
            'data' => $data
        ]);
    }

    public function create(CreateCustomerRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateCustomerRequest $request, $uuid)
    {
        $data = $this->service->edit(
            $request->validated(),
            $uuid
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'data' => $data
        ]);
    }

    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ]);
    }

    public function uploadCustomer(Request $request)
    {
        $file = $request->file('file');
        $this->service->uploadCustomers($file);

        return response()->json([
            'status' => 'success',
            'message' => 'File uploaded successfully'
        ]);
    }

    public function mergeCustomer(MergeCustomerRequest $request)
    {
        $this->service->mergeCustomer($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer merged successfully',
        ]);
    }

    public function getallCustomers(Request $request)
    {
        $data = $this->service->getallCustomers(
            $request->query('search_text'),
            $request->query('per_page')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Customers returned successfully',
            'data' => $data
        ]);
    }

    public function addBalToWallet(Request $request, $uuid)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $data = $this->service->addBalToWallet(
            $request->all(),
            $uuid
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Wallet updated successfully',
            'data' => $data
        ]);
    }

    public function exportCustomers(Request $request)
    {
        $request->validate([
            'startDate' => 'required',
            'endDate' => 'required'
        ]);

        return $this->service->exportCustomers($request->all());
    }
}
