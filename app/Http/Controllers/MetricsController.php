<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\MetricsService;

class MetricsController extends Controller
{
    private $service;

    public function __construct(MetricsService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function overviewNumbers(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $store_id = $request->query('store_id');

        $overviewNumbers = $this->service->overviewNumbers($startDate, $endDate, $store_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Overview numbers fetched successfully',
            'data' => $overviewNumbers
        ]);
    }

    public function dueOrders(Request $request)
    {
        $limit = $request->query('limit');

        $dueOrders = $this->service->dueOrders($limit);

        return response()->json([
            'status' => 'success',
            'message' => 'Due orders fetched successfully',
            'data' => $dueOrders
        ]);
    }

    public function revenuePage(Request $request)
    {
        $limit = $request->query('limit');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $revenuePage = $this->service->revenuePage($limit, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'message' => 'Revenue page fetched successfully',
            'data' => $revenuePage
        ]);
    }

    public function unpaidPage(Request $request)
    {
        $limit = $request->query('limit');

        $unpaidPage = $this->service->unpaidPage($limit);

        return response()->json([
            'status' => 'success',
            'message' => 'Unpaid page fetched successfully',
            'data' => $unpaidPage
        ]);
    }

    public function orderPage(Request $request)
    {
        $limit = $request->query('limit');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $orderPage = $this->service->orderPage($limit, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'message' => 'Order page fetched successfully',
            'data' => $orderPage
        ]);
    }

    public function customersPage(Request $request)
    {
        $limit = $request->query('limit');
        $count = $request->query('count');

        $customersPage = $this->service->customersPage($count, $limit);

        return response()->json([
            'status' => 'success',
            'message' => 'Customers page fetched successfully',
            'data' => $customersPage
        ]);
    }

    public function customersDetails(Request $request)
    {
        $limit = $request->query('limit');
        $isRecentOrder = $request->query('isRecentOrder');

        $customersDetails = $this->service->customersDetails($limit, $isRecentOrder);

        return response()->json([
            'status' => 'success',
            'message' => 'Customers details fetched successfully',
            'data' => $customersDetails
        ]);
    }

    public function cleaningPage(Request $request)
    {
        $startDatePeriod = $request->query('startDatePeriod');
        $endDatePeriod = $request->query('endDatePeriod');

        $cleaningPage = $this->service->cleaningPage($startDatePeriod, $endDatePeriod);

        return response()->json([
            'status' => 'success',
            'message' => 'Cleaning page fetched successfully',
            'data' => $cleaningPage
        ]);
    }

    public function walletFunding(Request $request)
    {
        $customer_id = $request->query('customer_id');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $walletFunding = $this->service->walletFunding($customer_id, $startDate, $endDate);

        return $walletFunding;
    }

    public function orderFinances(Request $request)
    {
        $order_id = $request->query('order_id');
        $customer_id = $request->query('customer_id');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $orderFinances = $this->service->orderFinances($order_id, $customer_id, $startDate, $endDate);

        return $orderFinances;
    }
}
