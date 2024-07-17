<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Item;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function index()
    {
        return Transaction::join('items', 'items.id', '=', 'transactions.item_id')
            ->join('customers', 'customers.id', '=', 'items.customer_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('product_options', 'product_options.id', '=', 'items.product_options_id')
            ->select(
                'transactions.id',
                'transactions.item_id',
                'items.tagId',
                'items.customer_id',
                'items.product_id',
                'items.product_option_id',
                'customers.full_name',
                'products.name as product_namne',
                'product_options.option_name',
                'product_options.price'
            )
            ->get();
    }

    public function view($uuid)
    {
        return Transaction::join('items', 'items.id', '=', 'transactions.item_id')
            ->join('customers', 'customers.id', '=', 'items.customer_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('product_options', 'product_options.id', '=', 'items.product_options_id')
            ->select(
                'transactions.id',
                'transactions.item_id',
                'items.tagId',
                'items.customer_id',
                'items.product_id',
                'items.product_option_id',
                'customers.full_name',
                'products.name as product_namne',
                'product_options.option_name',
                'product_options.price'
            )
            ->where('transactions.uuid', '=', $uuid)
            ->first();
    }

    public function edit($uuid, $data)
    {
        $transaction = Transaction::where('uuid', $uuid)->first();
        if (!$transaction) {
            throw new NotFoundException('Transaction not found');
        }

        $transaction->update($data);

        return $transaction;
    }

    public function delete($uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->first();
        if (!$transaction) {
            throw new NotFoundException('Transaction not found');
        }

        $transaction->delete();

        return $transaction;
    }

    public function trackItem($tagId)
    {
        $item = Item::where('tagId', 'like', '%' . $tagId . '%')->get();

        $ids = array_map(function ($single) {
            return $single["id"];
        }, $item->toArray());

        if (!count($item)) {
            return [];
        }

        return Transaction::join('items', 'items.id', '=', 'transactions.item_id')
            ->join('customers', 'customers.id', '=', 'items.customer_id')
            ->select(
                'transactions.*',
                'customers.full_name',
                "items.tagId",
                "items.brand as itemBrand",
                "items.extra_info as itemInfo",
                "items.description as itemDescription",
            )
            ->whereIn('item_id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function ordersPerHour($location = null)
    {
        return Transaction::when($location, function ($query) use ($location) {
            return $query->where('location_id', $location);
        })
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") AS hour')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();
    }

    public function ordersCompare(
        $previousPeriod = false,
        $lastMonth = false,
        $lastQuarter = false,
        $lastYear = false,
        $startDate = null,
        $endDate = null,
        $limit = 10
    ) {
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        $previousPeriod = filter_var($previousPeriod, FILTER_VALIDATE_BOOLEAN);
        $lastMonth = filter_var($lastMonth, FILTER_VALIDATE_BOOLEAN);
        $lastQuarter = filter_var($lastQuarter, FILTER_VALIDATE_BOOLEAN);
        $lastYear = filter_var($lastYear, FILTER_VALIDATE_BOOLEAN);

        $startDatePeriod2 = $startCarbon;
        $endDatePeriod2 = $endCarbon;

        if ($previousPeriod) {
            $startDatePeriod1 = $startCarbon->subWeek();
            $endDatePeriod1 = $endCarbon->subWeek();
        }

        if ($lastMonth) {
            $startDatePeriod1 = $startCarbon->subMonth();
            $endDatePeriod1 = $endCarbon->subMonth();
        }

        if ($lastQuarter) {
            $startDatePeriod1 = $startCarbon->subQuarter();
            $endDatePeriod1 = $endCarbon->subQuarter();
        }

        if ($lastYear) {
            $startDatePeriod1 = $startCarbon->subYear();
            $endDatePeriod1 = $endCarbon->subYear();
        }

        //1.Get For Sales
        $salesPeriod1 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(paidAmount) as total')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $salesPeriod2 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(paidAmount) as total')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $totalBillAmountPeriod1 = $salesPeriod1->sum('total');
        $totalBillAmountPeriod2 = $salesPeriod2->sum('total');
        $salesDiff = $totalBillAmountPeriod2 - $totalBillAmountPeriod1;
        $salesPercentageDiff = ($totalBillAmountPeriod1 + $totalBillAmountPeriod2 !== 0) ? ($salesDiff / (($totalBillAmountPeriod1 + $totalBillAmountPeriod2) / 2)) * 100 : 0;

        //2.Get Revenue
        $revenuePeriod1 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(revenue) as total')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $revenuePeriod2 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(revenue) as total')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $totalRevenueAmountPeriod1 = $revenuePeriod1->sum('total');
        $totalRevenueAmountPeriod2 = $revenuePeriod2->sum('total');
        $revenueDiff = $totalRevenueAmountPeriod2 - $totalRevenueAmountPeriod1;
        $revenuePercentageDiff = ($totalRevenueAmountPeriod1 + $totalRevenueAmountPeriod2 !== 0) ? ($revenueDiff / (($totalRevenueAmountPeriod1 + $totalRevenueAmountPeriod2) / 2)) * 100 : 0;

        //3.Get Orders
        $orderPeriod1 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $orderPeriod2 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $totalOrderAmountPeriod1 = $orderPeriod1->sum('total');
        $totalOrderAmountPeriod2 = $orderPeriod2->sum('total');
        $orderDiff = $totalOrderAmountPeriod2 - $totalOrderAmountPeriod1;
        $orderPercentageDiff = ($totalOrderAmountPeriod1 + $totalOrderAmountPeriod2 !== 0) ? ($orderDiff / (($totalOrderAmountPeriod1 + $totalOrderAmountPeriod2) / 2)) * 100 : 0;

        //Average Spend Per Customer
        $avgPeriod1 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('AVG(paidAmount) as total')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $avgPeriod2 = Order::where('status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('AVG(paidAmount) as total')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $totalAvgAmountPeriod1 = $avgPeriod1->sum('total');
        $totalAvgAmountPeriod2 = $avgPeriod2->sum('total');
        $avgDiff = $totalAvgAmountPeriod2 - $totalAvgAmountPeriod1;
        $avgPercentageDiff = ($totalAvgAmountPeriod1 + $totalAvgAmountPeriod2 !== 0) ? ($avgDiff / (($totalAvgAmountPeriod1 + $totalAvgAmountPeriod2) / 2)) * 100 : 0;

        //Popular Days
        $mostPopularDay1 = Order::where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->selectRaw('DAYNAME(created_at) as day_of_week, COUNT(*) as order_count')
            ->groupBy('day_of_week')
            ->orderBy(DB::raw('FIELD(day_of_week, "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")'))
            ->get();

        $mostPopularDay2 = Order::where('status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->selectRaw('DAYNAME(created_at) as day_of_week, COUNT(*) as order_count')
            ->groupBy('day_of_week')
            ->orderBy(DB::raw('FIELD(day_of_week, "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")'))
            ->get();

        $totalPopularPeriod1 = $mostPopularDay1->sum('order_count');
        $totalPopularPeriod2 = $mostPopularDay2->sum('order_count');
        $periodDiff = $totalPopularPeriod2 - $totalPopularPeriod1;
        $periodPercentageDiff = ($totalPopularPeriod1 + $totalPopularPeriod2 !== 0) ? ($periodDiff / (($totalPopularPeriod1 + $totalPopularPeriod2) / 2)) * 100 : 0;

        //New Customers
        $customerSet1 = Customer::selectRaw('DATE(created_at) AS register_date')
            ->selectRaw('COUNT(*) as customer_count')
            ->whereBetween('created_at', [$startDatePeriod1, $endDatePeriod1])
            ->groupBy('register_date')
            ->orderBy('register_date', 'asc')
            ->get();

        $customerSet2 = Customer::selectRaw('DATE(created_at) AS register_date')
            ->selectRaw('COUNT(*) as customer_count')
            ->whereBetween('created_at', [$startDatePeriod2, $endDatePeriod2])
            ->groupBy('register_date')
            ->orderBy('register_date', 'asc')
            ->get();

        $totalCustomerSet1 = $customerSet1->sum('customer_count');
        $totalCustomerSet2 = $customerSet2->sum('customer_count');
        $diff = $totalCustomerSet2 - $totalCustomerSet1;
        $percentageDiff = ($totalCustomerSet1 + $totalCustomerSet2 !== 0) ? ($diff / (($totalCustomerSet1 + $totalCustomerSet2) / 2)) * 100 : 0;

        // Retrieve customers with the highest order amounts
        $customersWithHighestOrders = Customer::leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('customers.id, customers.full_name, SUM(orders.paidAmount) as total_order_amount')
            ->groupBy('customers.id', 'customers.full_name')
            ->orderBy('total_order_amount', 'desc')
            ->limit($limit)
            ->get();

        // Retrieve top departments With Sales
        $topDepartmentsSales = Department::leftJoin('item_histories', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('orders', function ($join) {
                $join->on('item_histories.order_id', '=', 'orders.id')
                    ->where('item_histories.stage', '=', 'scan-out'); // Add your condition here
            })
            ->selectRaw(
                'departments.id, departments.name, 
                COUNT(item_histories.id) as total_order_amount, 
                SUM(orders.paidAmount) as total_paid_amount'
            )
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('total_order_amount', 'desc')
            ->limit($limit)
            ->get();

        // Retrieve top items With Sales
        $topItemsSales = Item::leftJoin('item_histories', function ($join) {
            $join->on('items.id', '=', 'item_histories.item_id')
                ->where('item_histories.stage', '=', 'scan-out');
        })
            ->leftJoin('product_options', 'items.product_option_id', '=', 'product_options.id')
            ->selectRaw(
                'items.id, 
                product_options.option_name, 
                COUNT(item_histories.id) as total_order_amount, 
                SUM(product_options.price) as total_paid_amount'
            )
            ->groupBy('items.id', 'product_options.option_name')
            ->orderBy('total_order_amount', 'desc')
            ->limit($limit)
            ->get();

        return [
            'sales' => [
                'percentageDifference' => $salesPercentageDiff,
                'sales-period-1' => [
                    'sales' =>  $salesPeriod1,
                    'sales-period1-total' => $totalBillAmountPeriod1,
                ],

                'sales-period-2' => [
                    'sales' => $salesPeriod2,
                    'sales-period2-total' => $totalBillAmountPeriod2
                ],
            ],
            'revenue' => [
                'percentageDifference' => $revenuePercentageDiff,
                'revenue-period-1' => [
                    'revenue' =>  $revenuePeriod1,
                    'revenue-period1-total' => $totalRevenueAmountPeriod1,
                ],

                'revenue-period-2' => [
                    'revenue' => $revenuePeriod2,
                    'revenue-period2-total' => $totalRevenueAmountPeriod2
                ],
            ],
            'orders' => [
                'percentageDifference' => $orderPercentageDiff,
                'order-period-1' => [
                    'order' =>  $orderPeriod1,
                    'order-period1-total' => $totalOrderAmountPeriod1,
                ],

                'order-period-2' => [
                    'order' => $orderPeriod2,
                    'order-period2-total' => $totalOrderAmountPeriod2
                ],
            ],
            'average' => [
                'percentageDifference' => $avgPercentageDiff,
                'avg-period-1' => [
                    'avg' =>  $avgPeriod1,
                    'avg-period1-total' => $totalAvgAmountPeriod1,
                ],

                'avg-period-2' => [
                    'avg' =>  $avgPeriod2,
                    'avg-period1-total' => $totalAvgAmountPeriod2,
                ],
            ],
            'popular' => [
                'percentageDifference' => $periodPercentageDiff,
                'popular-period-1' => [
                    'popular' =>  $mostPopularDay1,
                    'popular-period1-total' =>
                    $totalPopularPeriod1,
                ],

                'popular-period-2' => [
                    'popular' =>  $mostPopularDay2,
                    'popular-period2-total' =>
                    $totalPopularPeriod2,
                ],
            ],
            'customers' => [
                'percentageDifference' => $percentageDiff,
                'customer-set-1' => [
                    'customer' => $customerSet1,
                    'customer-set1-total' =>
                    $totalCustomerSet1,
                ],
                'customer-set-2' => [
                    'customer' => $customerSet2,
                    'customer-set2-total' =>
                    $totalCustomerSet2,
                ],
            ],
            'topCustomers' => [
                $customersWithHighestOrders
            ],
            'topSections' => [
                $topDepartmentsSales
            ],
            'topItems' => [
                $topItemsSales
            ],
        ];
    }
}
