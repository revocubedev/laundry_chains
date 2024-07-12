<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddColorBrandRequest;
use App\Http\Requests\AddItemSettingRequest;
use App\Http\Requests\CreateDamageRequest;
use App\Http\Requests\CreatePatternRequest;
use App\Http\Requests\UpdateDamageRequest;
use App\Http\Requests\UpdatePatternRequest;
use Illuminate\Http\Request;
use App\Services\ItemService;

class ItemController extends Controller
{
    private $service;

    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    public function addItemSetting(AddItemSettingRequest $request)
    {
        $data = $this->service->addItemSetting($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Items added successfully',
            'data' => $data
        ]);
    }

    public function getItemSetting()
    {
        $data = $this->service->getItemSetting();

        return response()->json([
            'status' => 'success',
            'message' => 'Items retrieved successfully',
            'data' => $data
        ]);
    }

    public function addColorBrand(AddColorBrandRequest $request)
    {
        $data = $this->service->addColorBrand($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Color and brand added successfully',
            'data' => $data
        ]);
    }

    public function createDamage(CreateDamageRequest $request)
    {
        $data = $this->service->createDamage($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Damage created successfully',
            'data' => $data
        ]);
    }

    public function getDamages()
    {
        $data = $this->service->getDamages();

        return response()->json([
            'status' => 'success',
            'message' => 'Damages retrieved successfully',
            'data' => $data
        ]);
    }

    public function editDamage(UpdateDamageRequest $request, $uuid)
    {
        $data = $this->service->editDamage($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Damage updated successfully',
            'data' => $data
        ]);
    }

    public function deleteDamage($uuid)
    {
        $this->service->deleteDamage($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Damage deleted successfully',
        ]);
    }

    public function createPattern(CreatePatternRequest $request)
    {
        $data = $this->service->createPattern($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Pattern created successfully',
            'data' => $data
        ]);
    }

    public function getPattern()
    {
        $data = $this->service->getPattern();

        return response()->json([
            'status' => 'success',
            'message' => 'Pattern retrieved successfully',
            'data' => $data
        ]);
    }

    public function editPattern(UpdatePatternRequest $request, $uuid)
    {
        $data = $this->service->editPattern($request->validated(), $uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Pattern updated successfully',
            'data' => $data
        ]);
    }

    public function deletePattern($uuid)
    {
        $this->service->deletePattern($uuid);

        return response()->json([
            'status' => 'success',
            'message' => 'Pattern deleted successfully',
        ]);
    }
}
