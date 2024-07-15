<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductGroupRequest;
use App\Http\Requests\CreateProductOptionRequest;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductGroupRequest;
use App\Http\Requests\UpdateProductOptionRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    private $service;

    public function __construct(ProductService $service)
    {
        $this->middleware('auth:api');
        $this->middleware('check.token');
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->service->index();

        return response()->json([
            "status" => "success",
            "message" => "Product successfully retrieved",
            "data" => $data
        ]);
    }

    public function all()
    {
        $data = $this->service->all();

        return response()->json([
            "status" => "success",
            "message" => "All products retrieved successfully",
            "data" => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateProductRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $data
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getProduct($uuid)
    {
        $data = $this->service->getProduct($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product retrieved successfully',
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
    public function edit(UpdateProductRequest $request, $uuid)
    {
        $data = $this->service->edit($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $this->service->destroy($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }

    public function get_product_groups()
    {
        $data = $this->service->get_product_groups();

        return response()->json([
            'status' => 'success',
            'message' => 'Product groups retrieved successfully',
            'data' => $data
        ]);
    }

    public function getSingleGroup($uuid)
    {
        $data = $this->service->getSingleGroup($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product group retrieved successfully',
            'data' => $data
        ]);
    }

    public function add_product_group(CreateProductGroupRequest $request)
    {
        $data = $this->service->add_product_group($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product group created successfully',
            'data' => $data
        ]);
    }

    public function edit_product_group(UpdateProductGroupRequest $request, $uuid)
    {
        $data = $this->service->edit_product_group($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product group updated successfully',
            'data' => $data
        ]);
    }

    public function delete_product_group($uuid)
    {
        $this->service->delete_product_group($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product group deleted successfully',
        ]);
    }

    public function getallProductOption()
    {
        $data = $this->service->getallProductOption();

        return response()->json([
            'status' => 'success',
            'message' => 'Product options retrieved successfully',
            'data' => $data
        ]);
    }

    public function get_product_options($product_id)
    {
        $data = $this->service->get_product_options($product_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Product options retrieved successfully',
            'data' => $data
        ]);
    }

    public function add_product_option(CreateProductOptionRequest $request)
    {
        $data = $this->service->add_product_option($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product option created successfully',
            'data' => $data
        ]);
    }

    public function edit_product_option(UpdateProductOptionRequest $request, $uuid)
    {
        $data = $this->service->edit_product_option($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product option updated successfully',
            'data' => $data
        ]);
    }

    public function delete_product_option($uuid)
    {
        $this->service->delete_product_option($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Product option deleted successfully',
        ]);
    }
}
