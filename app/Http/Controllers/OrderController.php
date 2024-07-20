<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoiceRequest;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\CreatePreOrderRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    private $service;

    public function __construct(OrderService $service)
    {
        $this->middleware('auth:api');
        $this->middleware('check.token');
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->index(
            $request->query('per_page'),
            $request->query('status'),
            $request->query('location'),
            $request->query('search_text'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Orders successfully retrieved',
            'data' => $data
        ]);
    }

    public function getByOrder($id)
    {
        $order = $this->service->getOrderBySerialNumber($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Order successfully retrieved',
            'data' => $order
        ]);
    }

    public function getByOrderID($id)
    {
        $order = $this->service->getByOrderId($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Order successfully retrieved',
            'data' => $order
        ]);
    }

    public function createOrder(CreateOrderRequest $request)
    {
        $data = $this->service->createOrder($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Order successfully created',
            'data' => $data
        ]);
    }

    public function createInvoice(CreateInvoiceRequest $request)
    {
        $data = $this->service->createInvoice($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice successfully created',
            'data' => $data
        ]);
    }

    public function show(Request $request)
    {
        $data = $this->service->show(
            $request->uuid,
            $request->status
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully',
            'data' => $data
        ]);
    }

    public function editOrderItem(UpdateOrderItemRequest $request)
    {
        $data = $this->service->editOrderItem($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $data
        ]);
    }

    public function addRack(Request $request, $uuid)
    {
        $data = $this->service->addRack($request->store_rack, $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Rack added successfully',
            'data' => $data
        ]);
    }

    public function edit(UpdateOrderRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $data
        ]);
    }

    public function getCustomerOrders(Request $request)
    {
        $data = $this->service->getCustomerOrders(
            $request->query('customerId'),
            $request->query('status'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Customer orders returned successfully',
            'data' => $data
        ]);
    }

    public function delete(Request $request, $uuid)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $this->service->delete($request->only([
            'reason'
        ]), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer order deleted successfully',
        ]);
    }

    public function dashboard(Request $request)
    {
        $data = $this->service->dashboard(
            $request->query('startDate'),
            $request->query('endDate'),
            $request->query('location')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard retrieved successfully',
            'data' => $data
        ]);
    }

    public function createPreOrder(CreatePreOrderRequest $request)
    {
        $data = $this->service->createPreOrder($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $data
        ]);
    }

    public function editInvoice(UpdateInvoiceRequest $request)
    {
        $data = $this->service->editInvoice($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice successfully updated',
            'data' => $data
        ]);
    }

    public function deleteInvoice(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $this->service->deleteInvoice($request->only(['id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice successfully created',
        ]);
    }

    public function allInvoice(Request $request)
    {
        $data = $this->service->allInvoice($request->query('status'));

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice successfully retrieved',
            'data' => $data
        ]);
    }

    public function markAsPaid(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $data = $this->service->markAsPaid($request->only(['id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice returned Successfully',
            'data' => $data
        ]);
    }

    public function updateByScan(Request $request)
    {
        $request->validate([
            'departmentId' => 'required|exists:departments,id',
            'orderId' => 'required|exists:orders,id',
            'stage' => 'required|string'
        ]);

        $data = $this->service->updateByScan($request->only([
            'departmentId',
            "orderId",
            "stage"
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated',
            'data' => $data
        ]);
    }

    public function createMovementList(Request $request)
    {
        $request->validate([
            'locationId' => 'required|exists:locations,id',
            'storeId' => 'required|exists:users,id',
            'driverId' => 'required|exists:users,id',
            'order_ids' => 'required|string',
            'total_bags' => 'required',
        ]);

        $data = $this->service->createMovementList($request->only([
            'locationId',
            "storeId",
            "order_ids",
            "total_bags",
            "driverId"
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Movement list created',
            'data' => $data
        ]);
    }

    public function getMovementList(Request $request)
    {

        $data = $this->service->getMovementList(
            $request->query('per_page'),
            $request->query('start_date'),
            $request->query('end_date')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Movement list returned',
            'data' => $data
        ]);
    }

    public function getSingleMovementList(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $data = $this->service->getSingleMovementList($request->only(['id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Movement list returned Successfully',
            'data' => $data
        ]);
    }

    public function markOrderPaid(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'isWallet' => 'required|boolean',
            'store_id' => 'required|exists:locations,id',
            'staff_id' => 'required|exists:users,id',
            'amount' => 'required',
        ]);

        $data = $this->service->markOrderPaid($request->only([
            'isWallet',
            "store_id",
            "staff_id",
            "amount",
            "driidverId"
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Order Payment Recorded',
            'data' => $data
        ]);
    }

    public function clone(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $location = $request->query('location');

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
