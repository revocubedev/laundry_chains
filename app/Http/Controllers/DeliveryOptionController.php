<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDeliveryOptionRequest;
use App\Http\Requests\CreateOrderOptionRequest;
use App\Http\Requests\UpdateDeliveryOptionRequest;
use App\Http\Requests\UpdateOrderOptionRequest;
use Illuminate\Http\Request;

use App\Services\DeliveryOptionService;

class DeliveryOptionController extends Controller
{
    private $service;

    public function __construct(DeliveryOptionService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $data = $this->service->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery options retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateDeliveryOptionRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery option created successfully',
            'data' => $data
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOne($uuid)
    {
        $data = $this->service->getOne($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery option retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(UpdateDeliveryOptionRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery option updated successfully',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($uuid)
    {
        $this->service->delete($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery option deleted successfully',
        ]);
    }

    public function createOrderOption(CreateOrderOptionRequest $request)
    {
        $data = $this->service->createOrderOption($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Order option created successfully',
            'data' => $data
        ]);
    }

    public function getOrderOption()
    {
        $data = $this->service->getOrderOption();

        return response()->json([
            'status' => 'success',
            'message' => 'Order option retrieved successfully',
            'data' => $data
        ]);
    }

    public function editOrderOption(UpdateOrderOptionRequest $request)
    {
        $data = $this->service->editOrderOption($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Order option updated successfully',
            'data' => $data
        ]);
    }
}
