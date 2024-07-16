<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\PaymentLog;
use App\Services\Helpers\Gen;
use App\Services\Helpers\Imports\CustomersImport;
use App\Services\Helpers\Exports\CustomerExport;
use App\Exceptions\NotFoundException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerService
{
    private $genService;

    public function __construct(Gen $genService)
    {
        $this->genService = $genService;
    }

    public function detail($uuid)
    {
        $customer = Customer::where('uuid', $uuid)->first();
        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        return $customer;
    }

    public function create($data)
    {
        return Customer::create($data);
    }

    public function edit($data, $uuid)
    {
        $customer = Customer::where('uuid', $uuid)->first();
        if (!$customer) {
            throw new NotFoundException('No Customer With That ID');
        }

        $customer->update($data);

        return $customer;
    }

    public function delete($uuid)
    {
        $customer = Customer::where('uuid', $uuid)->first();
        if (!$customer) {
            throw new NotFoundException('No Customer With That ID');
        }

        $customer->delete();
    }

    public function uploadCustomers($file)
    {
        Excel::import(new CustomersImport, $file);
    }

    public function mergeCustomer($data)
    {
        $fromCustomer = Customer::where('uuid', $data['fromCustomer'])->first();
        if (!$fromCustomer) {
            throw new NotFoundException('Incorrect details for customer to merge from');
        }

        $toCustomer = Customer::where('uuid', $data['toCustomer'])->first();
        if (!$toCustomer) {
            throw new NotFoundException('Incorrect details for customer to merge to');
        }

        DB::transaction(function () use ($fromCustomer, $toCustomer) {
            DB::table("items")->where("customer_id", "=", $fromCustomer["id"])->update(["customer_id" => $toCustomer["id"]]);
            DB::table("orders")->where("customer_id", "=", $fromCustomer["id"])->update(["customer_id" => $toCustomer["id"]]);
            DB::table("pre_orders")->where("customer_id", "=", $fromCustomer["id"])->update(["customer_id" => $toCustomer["id"]]);
            DB::table("customers")->where("id", "=", $toCustomer["id"])->update(["wallet" => (float) $toCustomer["wallet"] + (float) $fromCustomer["wallet"]]);
            DB::table("customers")->where("id", "=", $fromCustomer["id"])->delete();
        });
    }

    public function getAllCustomers($search_text = null, $per_page = 50)
    {
        return Customer::when($search_text, function ($query) use ($search_text) {
            return $query->where('full_Name', 'like', '%' . $search_text . '%')
                ->orWhere('email', 'like', '%' . $search_text . '%')
                ->orWhere('phone', 'like', '%' . $search_text . '%');
        })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page);
    }

    public function addBalToWallet($data, $uuid)
    {
        $authUser = auth()->user();

        $amount = $data['amount'];
        $staff_id = $authUser->id;
        $store_id = $authUser->location_id;

        $customer = Customer::where('uuid', $uuid)->first();
        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        $total = (float)$amount + (float)$customer->wallet;
        $customer->wallet = $total;
        $customer->save();

        PaymentLog::create([
            "customer_id" => $customer->id,
            "amount" => $amount,
            "method_of_payment" => "card",
            "location_id" => $store_id,
            "purpose" => "fund",
            "user_id" => $staff_id,
        ]);

        return $customer;
    }

    public function exportCustomers($data)
    {
        $startDate = $data['startDate'] ?? "2022-01-31";
        $endDate = $data['endDate'] ?? "3000-01-31";

        $customers = Order::join('customers', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('customers.id,customers.full_name,SUM(orders.paidAmount) AS total_paid_amount,SUM(orders.bill) AS total_amount,customers.wallet')
            ->where('orders.status', '!=', 'deleted')
            ->whereBetween('orders.created_at', [$startDate, Carbon::parse($endDate)->addDay()])
            ->groupBy('customers.id', 'customers.full_name', 'customers.wallet')
            ->get();

        return Excel::download(new CustomerExport($customers), 'customers.xlsx');
    }
}
