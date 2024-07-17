<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Transaction;
use App\Services\Helpers\Exports\OrderFinances;
use App\Services\Helpers\Exports\WalletFunding;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MetricsService
{
    public function overviewNumbers(
        $startDate = null,
        $endDate = null,
        $store_id = null
    ) {
        $today = Carbon::now();

        $startDate = $startDate ?? $today->toString();
        $endDate = $endDtae ?? $today->addYear(1)->toString();

        $orders = Order::where('orders.status', '!=', 'deleted')
            ->selectRaw(
                'COUNT(*) as order_count, 
                SUM(paidAmount) as value, SUM(revenue) as revenue, 
                SUM(discount + extra_discount_value) as discount, SUM(vat) as VAT, 
                SUM(CASE WHEN status = "ready" THEN itemsCount ELSE 0 END) as orders_cleaned'
            )
            ->when($store_id, function ($query) use ($store_id) {
                return $query->where('location_id', $store_id);
            })
            ->when($startDate != $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            }, function ($query) use ($startDate) {
                return $query->whereDate('created_at', $startDate);
            })
            ->get();

        $cleaned = Order::where('orders.status', 'ready')
            ->where('orders.status', '!=', 'deleted')
            ->join("transactions", "orders.id", "=", "transactions.order_id")
            ->join("items", "transactions.item_id", "=", "items.id")
            ->join("product_options", function ($join) {
                $join->on("items.product_option_id", "=", "product_options.id")
                    ->select(DB::raw('SUM(items.pieces) as total_pieces'));
            })
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->get();

        $totalPieces = 0;
        foreach ($cleaned as $cleanedItem) {
            // Ensure 'pieces' key exists in the current order item
            if (isset($cleanedItem['pieces'])) {
                // Convert 'pieces' to a float and add it to the total
                $totalPieces += (float)$cleanedItem['pieces'];
            }
        }
        $pieces = ["Pieces" => $totalPieces];
        $finalPiece = [$pieces];

        return [$orders, $finalPiece];
    }

    public function dueOrders($limit = 6)
    {
        $currentDate = Carbon::now();

        $overdueOrders = Order::where('orders.status', '!=', 'deleted')
            ->whereDate('dateTimeOut', '<', $currentDate)
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('orders.id, customers.full_name,orders.dateTimeOut,orders.paidAmount')
            ->paginate($limit);

        $dueToday = Order::where('orders.status', '!=', 'deleted')
            ->whereDate('dateTimeOut', '=', $currentDate)
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('orders.id, customers.full_name,orders.dateTimeOut,orders.paidAmount')
            ->paginate($limit);

        $dueTomorrow = Order::where('orders.status', '!=', 'deleted')
            ->whereDate('dateTimeOut', '=', $currentDate->copy()->addDay())
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('orders.id, customers.full_name,orders.dateTimeOut,orders.paidAmount')
            ->paginate($limit);

        $discount = Order::where('orders.status', '!=', 'deleted')
            ->where('orders.discount', '>', 0)
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('orders.id, customers.full_name,orders.dateTimeOut,orders.discount')
            ->groupBy('orders.id')
            ->paginate($limit);

        $staffs = Order::where('orders.status', '!=', 'deleted')
            ->leftJoin('users', 'orders.staff_id', '=', 'users.id')
            ->selectRaw(
                'users.fullName,SUM(paidAmount) as sales, 
                SUM(revenue) as revenue, COUNT(CASE WHEN status = "ready" THEN 1 ELSE 0 END) as clean, 
                COUNT(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as ready'
            )
            ->groupBy('orders.staff_id')
            ->get();

        return [
            'orders' => $overdueOrders,
            'dueToday' => $dueToday,
            'dueTomorrow' => $dueTomorrow,
            'discount' => $discount,
            '$staffs' => $staffs
        ];
    }

    public function revenuePage($limit = 6, $startDate, $endDate)
    {
        //1.Get Revenue Chart
        $revenuePeriod = Order::where('orders.status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(revenue) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        //2. Get All Revenue
        $revenue = Order::where('orders.status', '!=', 'deleted')
            ->selectRaw(
                'DATE_FORMAT(created_at, "%Y-%m-%d") AS Date,
                revenue,
                SUM(CASE WHEN paymentType = "card" THEN revenue ELSE 0 END) as card_revenue,
                SUM(CASE WHEN paymentType = "cash" THEN revenue ELSE 0 END) as cash_revenue,
                SUM(CASE WHEN paymentType = "wallet" THEN revenue ELSE 0 END) as wallet_revenue,
                SUM(CASE WHEN paymentType = "bank" THEN revenue ELSE 0 END) as bank_revenue,
                SUM(CASE WHEN paymentType = "cheque" THEN revenue ELSE 0 END) as cheque_revenue'
            )
            ->groupBy('Date', 'revenue')
            ->paginate($limit);

        return [
            'revenueChart' => $revenuePeriod,
            'revenue' => $revenue
        ];
    }

    public function unpaidPage($limit = 6)
    {
        $unpaidNumber = Order::where('orders.status', '!=', 'deleted')->where('is_paid', 0)
            ->selectRaw('SUM(paidAmount) as total')->get();

        $unpaidOrders = Order::where('orders.status', '!=', 'deleted')->where('is_paid', 0)
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('SUM(paidAmount) as total, customers.full_name, COUNT(*) as Orders')
            ->groupBy('orders.id')
            ->paginate($limit);

        return [
            'unPaidNumbers' => $unpaidNumber,
            'unpaidOrders' => $unpaidOrders
        ];
    }

    public function orderPage($limit = 10, $startDate, $endDate)
    {
        //1.Get Order Chart
        $orderPeriod = Order::where('orders.status', '!=', 'deleted')
            ->selectRaw('DATE(created_at) AS order_date')
            ->selectRaw('SUM(paidAmount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        //2. Get All Order
        $orders = Order::where('orders.status', '!=', 'deleted')
            ->selectRaw(
                'DATE_FORMAT(created_at, "%Y-%m-%d") AS Date,
                SUM(paidAmount) as Sales,
               COUNT(*) as Orders'
            )
            ->groupBy('Date')
            ->paginate($limit);

        return [
            'orderChart' => $orderPeriod,
            'orders' => $orders
        ];
    }

    public function customersPage($count = 10, $limit = 10)
    {
        $currentDate = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $newCustomers = Customer::whereBetween('created_at', [$lastMonth, $currentDate])
            ->selectRaw('COUNT(*) as newCustomersCount')
            ->get();

        $customersCount = Customer::selectRaw('COUNT(*) as total_customers')
            ->get();

        $customers = Customer::leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('customers.id, customers.full_name, COUNT(orders.id) as total_order_amount, SUM(orders.revenue) as total_revenue, SUM(orders.paidAmount) as total_sales')
            ->groupBy('customers.id', 'customers.full_name')
            ->get();

        // Retrieve customers with the highest order amounts
        $customersWithHighestOrders = Customer::leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('customers.id, customers.full_name, COUNT(orders.id) as customer_total_orders, SUM(orders.paidAmount) as total_order_amount, SUM(orders.revenue) as total_revenue')
            ->groupBy('customers.id', 'customers.full_name')
            ->orderBy('total_order_amount', 'desc')
            ->limit($count)
            ->paginate($limit);

        return [
            'totalCustomers' => $customersCount,
            'totalCustomersChart' => $customers,
            'newCustomers' => $newCustomers,
            'topCustomers' => $customersWithHighestOrders
        ];
    }

    public function customersDetails($limit = 10, $isRecentOrder = false)
    {
        $currentDate = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $isRecentOrder = filter_var($isRecentOrder, FILTER_VALIDATE_BOOLEAN);

        $newCustomers = Customer::whereBetween('customers.created_at', [$lastMonth, $currentDate])
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw(
                'customers.full_name,
                customers.email,
                customers.phone,
                customers.created_at,
                MAX(orders.created_at) as last_order_date'
            )
            ->when($isRecentOrder, function ($query) {
                return $query->selectRaw('SUM(orders.paidAmount) as total_order_amount');
            }, function ($query) {
                return $query->selectRaw('orders.customer_id as customerID');
            })
            ->groupBy('customers.id', 'customers.full_name', 'customers.email', 'customers.phone')
            ->orderBy('customers.created_at', 'desc')
            ->paginate($limit);

        return [
            'newCustomers' => $newCustomers,
        ];
    }

    public function cleaningPage($startDatePeriod, $endDatePeriod)
    {
        $mostPopular = Order::where('orders.status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDatePeriod, $endDatePeriod])
            ->selectRaw('DAYNAME(created_at) as day_of_week, SUM(orders.paidAmount) as price')
            ->groupBy('day_of_week')
            ->orderBy(DB::raw('FIELD(day_of_week, "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")'))
            ->get();

        $mostPopularHour = Order::where('orders.status', '!=', 'deleted')
            ->whereBetween('created_at', [$startDatePeriod, $endDatePeriod])
            ->selectRaw('CONCAT(HOUR(created_at), " ", IF(HOUR(created_at) < 12, "AM", "PM")) as hour_of_day, SUM(orders.paidAmount) as price')
            ->groupBy('hour_of_day')
            ->orderBy('hour_of_day')
            ->get();

        $items = Transaction::leftJoin('products', 'transactions.product_id', '=', 'products.id')
            ->join('product_groups', 'products.product_group_id', '=', 'product_groups.id')
            ->join('product_options', 'transactions.product_id', '=', 'product_options.id')
            ->join('orders', 'transactions.order_id', '=', 'orders.id')
            ->join('users', 'orders.staff_id', '=', 'users.id')
            ->selectRaw(
                'MAX(products.name) as product_name, 
                MAX(product_groups.group_name) as group_name, 
                transactions.created_at, COUNT(transactions.id) as Quantity, 
                SUM(product_options.price) as Sales, users.fullName as staff, 
                COUNT(product_options.pieces) as pieces'
            )
            ->groupBy('transactions.created_at', 'users.fullName')
            ->get();

        return [
            "days" =>  $mostPopular,
            "hour" =>  $mostPopularHour,
            "items" => $items
        ];
    }

    public function walletFunding($customer_id = null, $startDate = null, $endDate = null)
    {
        $data = PaymentLog::where("purpose", "fund")
            ->select("payment_logs.*", "customers.full_name", "locations.locationName as store", "users.fullName as staff")
            ->join("locations", "locations.id", "=", "payment_logs.location_id")
            ->join("users", "users.id", "=", "payment_logs.user_id")
            ->join("customers", "customers.id", "=", "payment_logs.customer_id")
            ->when($customer_id, function ($query) use ($customer_id) {
                return $query->where("payment_logs.customer_id", $customer_id);
            })
            ->when($startDate, function ($query) use ($startDate) {
                return $query->where("payment_logs.created_at", ">=", $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->where("payment_logs.created_at", "<=", Carbon::parse($endDate)->addDay());
            })
            ->get();

        $exports = new WalletFunding($data);
        return Excel::download($exports, "Wallet Funding Report.xlsx");
    }

    public function orderFinances(
        $order_id = null,
        $customer_id = null,
        $startDate = null,
        $endDate = null
    ) {
        $data = PaymentLog::where("purpose", "payment")
            ->select("payment_logs.*", "customers.full_name", "orders.bill", "orders.serial_number", "locations.locationName as store", "users.fullName as staff")
            ->join("locations", "locations.id", "=", "payment_logs.location_id")
            ->join("users", "users.id", "=", "payment_logs.user_id")
            ->join("customers", "customers.id", "=", "payment_logs.customer_id")
            ->join("orders", "orders.id", "=", "payment_logs.order_id")
            ->when($order_id, function ($query) use ($order_id) {
                return $query->where("orders.serial_number", $order_id);
            })
            ->when($customer_id, function ($query) use ($customer_id) {
                return $query->where("payment_logs.customer_id", $customer_id);
            })
            ->when($startDate, function ($query) use ($startDate) {
                return $query->where("payment_logs.created_at", ">=", $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->where("payment_logs.created_at", "<=", Carbon::parse($endDate)->addDay());
            })
            ->get();

        $exports = new OrderFinances($data);
        return Excel::download($exports, "Order Payment Report.xlsx");
    }
}
