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

    public function createDiscountType(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'percentage' => 'required',
        ]);

        $discountType = $this->service->createDiscountType($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Discount type created successfully',
            'data' => $discountType
        ]);
    }

    public function editDiscountType(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'nullable|string',
            'percentage' => 'nullable',
        ]);

        $discountType = $this->service->editDiscountType($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Discount type updated successfully',
            'data' => $discountType
        ]);
    }

    public function getDiscountTypes()
    {
        $discountTypes = $this->service->getDiscountTypes();

        return response()->json([
            'status' => 'success',
            'message' => 'Discount types fetched successfully',
            'data' => $discountTypes
        ]);
    }

    public function createCharge(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'value' => 'required|numeric',
        ]);

        $charge = $this->service->createCharge($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Charge created successfully',
            'data' => $charge
        ]);
    }

    public function editCharge(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'nullable|string',
            'value' => 'nullable|numeric',
        ]);

        $charge = $this->service->editCharge($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Charge updated successfully',
            'data' => $charge
        ]);
    }

    public function getCharges()
    {
        $charges = $this->service->getCharges();

        return response()->json([
            'status' => 'success',
            'message' => 'Charges fetched successfully',
            'data' => $charges
        ]);
    }
}
