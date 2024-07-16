<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportsService;

class ReportsController extends Controller
{
    private $service;

    public function __construct(ReportsService $service)
    {
        $this->middleware('auth:api', ['except' => ['generateItemReport', 'salesReport']]);
        $this->service = $service;
    }

    public function getTotalNumbers(Request $request)
    {
        $data = $this->service->getTotalNumbers(
            $request->startDate,
            $request->endDate,
            $request->locationId,
            $request->productId
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Total numbers fetched successfully',
            'data' => $data
        ]);
    }

    public function getGarmentsNumbers(Request $request)
    {
        $data = $this->service->getGarmentsNumbers(
            $request->startDate,
            $request->endDate,
            $request->departmentId,
            $request->productId
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Garments numbers fetched successfully',
            'data' => $data
        ]);
    }

    public function totalGarmentsDepartment(Request $request)
    {
        $data = $this->service->totalGarmentsDepartment(
            $request->date,
            $request->departmentId
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Total garments department fetched successfully',
            'data' => $data
        ]);
    }

    public function ItemsByIndividual(Request $request)
    {
        $data = $this->service->itemsByIndividual(
            $request->staffId,
            $request->startDate,
            $request->endDate,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Items by individual fetched successfully',
            'data' => $data
        ]);
    }

    public function totalLeftFactory(Request $request)
    {
        $data = $this->service->totalLeftFactory(
            $request->date,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Total left factory fetched successfully',
            'data' => $data
        ]);
    }

    public function generateItemReport(Request $request)
    {
        return $this->service->generateItemReport(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('department_id'),
            $request->query('department'),
        );
    }

    public function salesReport(Request $request)
    {
        return $this->service->salesReport(
            $request->query('start_date'),
            $request->query('end_date'),
        );
    }
}
