<?php

namespace App\Services;

use App\Models\ItemHistory;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Helpers\Exports\DepartmentReport;
use App\Services\Helpers\Exports\SalesReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ReportsService
{
    private $today;
    private $oneYearFromToday;

    public function __construct()
    {
        $this->today = Carbon::now();
        $this->oneYearFromToday = Carbon::now()->addYear(1);
    }

    private function mergeScannedData($scannedInData, $scannedOutData)
    {
        $scannedOutLookup = [];
        foreach ($scannedOutData as $scannedOutItem) {
            $scannedOutLookup[$scannedOutItem['tagId']] = $scannedOutItem;
        }

        $mergedData = [];
        foreach ($scannedInData as $scannedInItem) {
            $tagId = $scannedInItem['tagId'];
            $scannedOutItem = isset($scannedOutLookup[$tagId]) ? $scannedOutLookup[$tagId] : null;
            $mergedData[] = ['scannedIn' => $scannedInItem, 'scannedOut' => $scannedOutItem];
            unset($scannedOutLookup[$tagId]);
        }

        foreach ($scannedOutLookup as $key => $scannedOutItem) {
            $mergedData[] = ['scannedIn' => null, 'scannedOut' => $scannedOutItem];
        }

        return $mergedData;
    }

    public function getTotalNumbers($startDate = null, $endDate = null, $locationId = null, $productId = null)
    {
        $startDate = $startDate ?? $this->today;
        $endDate = $endDate ?? $this->oneYearFromToday;

        $items = Transaction::with('location', 'product', 'item')
            ->when($locationId, function ($query) use ($locationId) {
                return $query->where('location_id', $locationId);
            })
            ->when($productId, function ($query) use ($productId) {
                return $query->where('product_id', $productId);
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => $items->count(),
            'items' => $items->get()
        ];
    }

    public function getGarmentsNumbers($startDate = null, $endDate = null, $departmentId = null, $productId = null)
    {
        $startDate = $startDate ?? $this->today;
        $endDate = $endDate ?? $this->oneYearFromToday;

        $items = ItemHistory::leftJoin('departments', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('items', 'items.id', '=', 'item_histories.item_id')
            ->leftJoin('products', 'products.id', '=', 'item_histories.product_id')
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->where('item_histories.department_id', $departmentId);
            })
            ->when($productId, function ($query) use ($productId) {
                return $query->where('item_histories.product_id', $productId);
            })
            ->whereBetween('item_histories.created_at', [$startDate, $endDate])
            ->selectRaw('item_histories.department_id, departments.id, departments.name as department_name,COUNT(*) as total')
            ->groupBy('item_histories.department_id', 'departments.name', 'products.name');

        return [
            'total' => $items->count(),
            'items' => $items->get()
        ];
    }

    public function totalGarmentsDepartment($date = null, $departmentId = null)
    {
        $dateWithTime = Carbon::parse($date) ?? $this->today;

        // Add 24 hours (1 day) to the date
        $dateWithTime->addHours(24);

        $items = ItemHistory::leftJoin('items', 'items.id', '=', 'item_histories.item_id')
            ->leftJoin('departments', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('products', 'products.id', '=', 'item_histories.product_id')
            ->whereBetween('item_histories.created_at', [$date, $dateWithTime])
            ->selectRaw('item_histories.department_id,departments.name as department_name, products.name as product_type, COUNT(item_histories.id) as number_of_items')
            ->groupBy('item_histories.department_id', 'departments.name', 'products.name')
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->where('item_histories.department_id', $departmentId);
            });

        return [
            'total' => $items->count(),
            'items' => $items->get()
        ];
    }

    public function itemsByIndividual($staffId = null, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? $this->today;
        $endDate = $endDate ?? $this->oneYearFromToday;

        $items = ItemHistory::leftJoin('items', 'items.id', '=', 'item_histories.item_id')
            ->leftJoin('orders', 'orders.id', '=', 'item_histories.order_id')
            ->leftJoin('departments', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('users', 'users.id', '=', 'item_histories.staff_id')
            ->leftJoin('products', 'products.id', '=', 'item_histories.product_id')
            ->whereBetween('item_histories.created_at', [$startDate, $endDate])
            ->when($staffId, function ($query) use ($staffId) {
                return $query->where('item_histories.staff_id', $staffId);
            })
            ->selectRaw(
                'users.fullName as staff_name, 
                departments.name as department, 
                COUNT(orders.itemsCount) as number_of_pieces, 
                DATE(item_histories.created_at) as Date'
            )
            ->groupBy(
                'staff_name',
                'department',
                'Date'
            )
            ->get();

        return [
            'total' => count($items),
            'items' => $items
        ];
    }

    public function totalLeftFactory($date)
    {
        $dateWithTime = Carbon::parse($date) ?? $this->today;

        // Add 24 hours (1 day) to the date
        $dateWithTime->addHours(24);

        $items = Order::where('status', 'completed')
            ->leftJoin("locations", "locations.id", "=", "orders.location_id")
            ->whereBetween('orders.created_at', [$date, $dateWithTime])
            ->selectRaw(
                'orders.id as order_id, 
                SUM(orders.itemsCount) as number_of_pieces, 
                locations.locationName'
            )
            ->groupBy('orders.id')
            ->get();

        return [
            'total' => count($items),
            'items' => $items
        ];
    }

    //Generate Report For Each Departments 
    public function generateItemReport($start_date = null, $end_date = null, $departmentId, $departmentName)
    {
        $startDate = $start_date ?? $this->today;
        $endDate = $end_date ?? $this->oneYearFromToday;

        $scanInData = ItemHistory::where('item_histories.department_id', $departmentId)
            ->leftJoin("orders", "orders.id", "=", "item_histories.order_id")
            ->leftJoin("departments", "departments.id", "=", "item_histories.department_id")
            ->leftJoin("users", "users.id", "=", "item_histories.staff_id")
            ->leftJoin("items", "items.id", "=", "item_histories.item_id")
            ->leftJoin("product_options", "product_options.id", "=", "items.product_option_id")
            ->leftJoin("products", "products.id", "=", "items.product_id")
            ->whereBetween('item_histories.created_at', [$startDate, Carbon::parse($endDate)->addDay()])
            ->where('item_histories.stage', 'scan-in')
            ->select(
                'item_histories.*',
                'orders.dateTimeIn',
                'departments.scan_in_out',
                'orders.serial_number',
                'orders.dateTimeOut',
                'items.tagId',
                'items.brand',
                'items.extra_info',
                'products.name',
                'users.fullName',
                "product_options.option_name"
            )
            ->get();

        $scanOutData = ItemHistory::where('item_histories.department_id', $departmentId)
            ->leftJoin("orders", "orders.id", "=", "item_histories.order_id")
            ->leftJoin("departments", "departments.id", "=", "item_histories.department_id")
            ->leftJoin("users", "users.id", "=", "item_histories.staff_id")
            ->leftJoin("items", "items.id", "=", "item_histories.item_id")
            ->leftJoin("product_options", "product_options.id", "=", "items.product_option_id")
            ->leftJoin("products", "products.id", "=", "items.product_id")
            ->where('item_histories.status', "=", 'scan-out')
            ->whereBetween('item_histories.created_at', [$startDate, Carbon::parse($endDate)->addDay()])
            ->select(
                'item_histories.*',
                'orders.dateTimeIn',
                'departments.scan_in_out',
                'orders.serial_number',
                'orders.dateTimeOut',
                'items.tagId',
                'items.brand',
                'items.extra_info',
                'products.name',
                'users.fullName',
                "product_options.option_name"
            )
            ->get();

        $mergedData = $this->mergeScannedData($scanInData, $scanOutData);
        $exports = new DepartmentReport($mergedData);
        return Excel::download($exports, "{$departmentName}.xlsx");
    }

    public function salesReport($start_date = null, $end_date = null)
    {
        $startDate = $start_date ?? $this->today;
        $endDate = $end_date ?? $this->oneYearFromToday;

        if ($startDate != $endDate) {
            $order = Order::leftJoin('users', 'users.id', '=', 'orders.staff_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
                ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->addDay()])
                ->select('orders.*', 'customers.*', 'users.fullName', 'locations.store_code')
                ->get();
        } else {
            $order = Order::leftJoin('users', 'users.id', '=', 'orders.staff_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->whereDate('orders.created_at', [$startDate, $endDate])
                ->get();
        }

        $order = Order::leftJoin('users', 'users.id', '=', 'orders.staff_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->when($startDate != $endDate, function ($query) use ($startDate, $endDate) {
                return $query->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
                    ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->addDay()])
                    ->select('orders.*', 'customers.*', 'users.fullName', 'locations.store_code');
            }, function ($query) use ($startDate) {
                return $query->whereDate('orders.created_at', $startDate);
            })
            ->get();

        $exports = new SalesReport($order);
        return Excel::download($exports, 'salesReport.xlsx');
    }
}
