<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Models\Charges;
use App\Models\Customer;
use App\Models\DeletedOrder;
use App\Models\DiscountType;
use App\Models\Order;
use App\Models\Location;
use App\Models\DeliveryOptions;
use App\Models\Department;
use App\Models\Item;
use App\Models\MovementList;
use App\Models\OrderInvoice;
use App\Models\PaymentLog;
use App\Models\PreOrder;
use App\Models\ProductOption;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Services\Helpers\Gen;
use App\Services\Helpers\MailService;

class OrderService
{
    private $generate;
    private $mailService;

    public function __construct(Gen $generate, MailService $mailService)
    {
        $this->mailService = $mailService;
        $this->generate = $generate;
    }

    public function index($per_page = 50, $status = null, $location = null, $search_text = null)
    {
        return Order::with('customer', 'location', 'transactions.item.product')
            ->when($status, function ($query) use ($status, $search_text) {
                return $query->where('status', $status)
                    ->orderBy('orders.created_at', 'DESC')
                    ->where(function ($query) use ($search_text) {
                        $query->whereHas('customer', function ($subQuery) use ($search_text) {
                            $subQuery->where('full_name', 'like', '%' . $search_text . '%');
                        })
                            ->orWhere('serial_number', 'like', '%' . $search_text . '%');
                    });
            })
            ->when($location, function ($query) use ($location, $search_text) {
                return $query->where('location_id', $location)
                    ->orderBy('orders.created_at', 'DESC')
                    ->where(function ($query) use ($search_text) {
                        $query->whereHas('customer', function ($subQuery) use ($search_text) {
                            $subQuery->where('full_name', 'like', '%' . $search_text . '%');
                        })
                            ->orWhere('serial_number', 'like', '%' . $search_text . '%');
                    });
            })
            ->paginate($per_page);
    }

    public function getOrderBySerialNumber($id)
    {
        $order = Order::with(
            'customer',
            'location',
            'transactions.charge',
            'transactions.item.product',
            'transactions.item.product_option',
            'user'
        )
            ->where("serial_number", $id)
            ->first();
        if (!$order) {
            throw new NotFoundException('Order not found');
        }

        return $order;
    }

    public function getByOrderId($uuid)
    {
        $order = Order::with(
            'customer',
            'location',
            'transactions.charge',
            'transactions.item.product',
            'transactions.item.product_option',
            'user'
        )
            ->where("uuid", $uuid)
            ->first();
        if (!$order) {
            throw new NotFoundException('Order not found');
        }

        return $order;
    }

    public function createOrder($data)
    {
        $extraDiscount = 1;

        if (isset($data['extra_discount_id'])) {
            $discountPackage = DiscountType::where('id', $data['extra_discount_id'])->first();
            if (!$discountPackage) {
                throw new NotFoundException('Discount type not found');
            }

            $extraDiscount = $discountPackage->percentage;
        }

        $deliveryPrice = DeliveryOptions::where('id', $data['deliveryId'])->first();
        if (!$deliveryPrice) {
            throw new NotFoundException('Delivery option not found');
        }

        $storeInfo = Location::where('id', $data['store_id'])->first();
        if (!$storeInfo) {
            throw new NotFoundException('Location not found');
        }

        $customer =  Customer::where('id', $data['customer_id'])->first();
        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }

        $previousOrder = Order::where("location_id", $data['store_id'])->count();

        [$totalPrice, $revenue] = $this->calcTotalOrderTotalAndRevenue($data['order_items'], $data['isExpress']);

        $customerDiscount = isset($data['discount_percentage']) ? $totalPrice * ($data['discount_percentage'] / 100) : 0;

        $data['revenue'] = $revenue;

        $discountedAmount = $totalPrice - $customerDiscount;

        $data['discount'] = $customerDiscount;

        $data['paidAmount'] = (bool) $data['is_paid'] ? $discountedAmount * $extraDiscount + (float) $deliveryPrice->amount : $data['paidAmount'];

        $data['vat'] = (7.5 * 100) / $totalPrice;

        $data['serial_number'] = $storeInfo["store_code"] . '-' . ($previousOrder + 1);

        $data['extra_discount_percentage'] = $extraDiscount;

        $data['extra_discount_value'] = $discountedAmount * $extraDiscount;

        $data['dateTimeIn'] = Carbon::now();

        $data['bill'] = $discountedAmount * $extraDiscount + (float) $deliveryPrice->amount;

        $data['location_id'] = $data['store_id'];

        $data['status'] = "processing";

        $data['staff_id'] = $data['staffId'];

        $data['delivery_id'] = $data['deliveryId'];

        if ($data['paymentType'] == 'wallet') {
            if ($customer->wallet < $data['paidAmount']) {
                throw new NotFoundException('Insufficient balance');
            }

            $customer->wallet -= $data['paidAmount'];
            $customer->save();
        }

        $order = Order::create($data);
        $order_id = $order->id;

        PaymentLog::create([
            "customer_id" => $data['customer_id'],
            "order_id" => $order_id,
            "amount" => $data['paidAmount'],
            "method_of_payment" => $data['paymentType'],
            "location_id" => $data['store_id'],
            "purpose" => "payment",
            "user_id" => $data['staffId'],
        ]);

        //create each item in the order - use transaction
        $order_items = $data['order_items'];
        $customerId = $order->customer_id;

        foreach ($order_items as $order_item) {
            $result =  $this->createItem($order_id, $order_item, $customerId, $order);

            if ($result["status"] === "error") {
                $order->delete(); //RollBack Order Creation
                throw new BadRequestException($result['message']);
            }
        }

        $user = Customer::where('id', $data['customer_id'])->first();

        $this->mailService->sendCreationNotificationEmail([
            'to' => $user->email,
            'content' => [
                'user' => $user,
                'url' => tenant('organisation_url'),
                'logo' => tenant('organisation_logo'),
                'order' => $order,
                'companyName' => tenant('organisation_name')
            ]
        ]);

        return $order;
    }

    public function show($uuid = null, $status = null)
    {
        if ($uuid) {
            $order = Order::where('uuid', $uuid)->first();
            if (!$order) {
                throw new NotFoundException('No order found');
            }

            $order_id = $order->id;

            $deletedOrder = null;
            if ($order->status === "deleted") {
                $deletedOrder = DeletedOrder::where("order_id", $order_id)->first();
            }

            $order_items = Transaction::join('orders', 'transactions.order_id', '=', 'orders.id')
                ->join('items', 'items.id', '=', 'transactions.item_id')
                ->join('products', 'products.id', '=', 'transactions.product_id')
                ->join('product_options', 'product_options.id', '=', 'items.product_option_id')
                ->where('orders.id', '=', $order_id)
                ->select(
                    'items.*',
                    'transactions.id as transaction_id',
                    'transactions.uuid as transaction_code',
                    'products.name',
                    'product_options.pieces',
                    'product_options.option_name as option_name',
                    'transactions.number'
                )
                ->get();

            return ["data" => $order_items, "deleted" => $deletedOrder];
        }

        if ($status) {
            $order = Order::where('status', $status)->first();
            if (!$order) {
                throw new NotFoundException('No order found');
            }
            $order_id = $order->id;

            $deletedOrder = null;
            if ($order->status === "deleted") {
                $deletedOrder = DeletedOrder::where("order_id", $order_id)->first();
            }

            $order_items = Transaction::join('orders', 'transactions.order_id', '=', 'orders.id')
                ->join('items', 'items.id', '=', 'transactions.item_id')
                ->where('orders.id', '=', $order_id)
                ->select(
                    'items.*',
                    'transactions.id as transaction_id',
                    'transactions.uuid as transaction_code'
                )
                ->get();

            return ["data" => $order_items, "deleted" => $deletedOrder];
        }
    }

    public function editOrderItem($data)
    {
        $itemId = $data['itemId'];

        $item = Item::where('id', $itemId)->first();
        if (!$item) {
            throw new NotFoundException('Item not found');
        }

        $item->update($data);

        return $item;
    }

    public function edit($data, $uuid)
    {
        $paymentType = $data['paymentType'];
        $customer_id = $data['customer_id'];
        $staffId  = $data['staffId'];
        $paidAmount  = $data['paidAmount'];
        $store_id  = $data['store_id'];

        $order = Order::where('uuid', $uuid)->first();
        if (!$order) {
            throw new NotFoundException('No order found');
        }

        if ((float) $order->paidAmount !== (float) $paidAmount) {
            $amount = (float) $paidAmount - (float) $order->paidAmount;
            PaymentLog::create([
                "customer_id" => $customer_id,
                "order_id" => $order->id,
                "amount" => $amount,
                "method_of_payment" => $paymentType,
                "location_id" => $store_id,
                "purpose" => "payment",
                "user_id" => $staffId,
            ]);
        }

        $order->update($data);

        //create each item in the order - use transaction
        $order_id = $order->id;
        $customerId = $order->customer_id;

        if (isset($data['order_items'])) {
            foreach ($data['order_items'] as $order_item) {
                $this->createItem($order_id, $order_item, $customerId, $order);
            }
        }

        $extra_discount_id = isset($data['extra_discount_id']) ? $data['extra_discount_id'] : $order->extra_discount_id;
        $discount_percentage = isset($data['discount_percentage']) ? $data['discount_percentage'] : $order->discount_percentage;

        //Handle discount values
        $discount = DiscountType::find($extra_discount_id);
        if (!$discount) {
            throw new NotFoundException('Discount type not found');
        }

        $extraDiscount = (float) $discount->percentage;

        //Fetch Delivery
        $deliveryPrice = DeliveryOptions::where('id', $order->delivery_id)->first();
        if (!$deliveryPrice) {
            throw new NotFoundException('Delivery option not found');
        }

        //Fetch Old Order and Recalculate 
        $initialOrderItemBill = ((float) ($order->bill - (float) $deliveryPrice["amount"]) / (float) $order->extra_discount_percentage) + (float) $order->discount;

        $discountPrice = $initialOrderItemBill * ($discount_percentage / 100);
        $discountedAmount = $initialOrderItemBill - $discountPrice;
        $order->discount_percentage = $discount_percentage;
        $order->discount = $discountPrice;
        $order->extra_discount_percentage = $extraDiscount;
        $order->extra_discount_value = $discountedAmount * $extraDiscount;
        $order->bill = $discountedAmount * $extraDiscount + $deliveryPrice->amount;
        $order->paidAmount = isset($data['is_paid']) ? $discountedAmount * $extraDiscount + $deliveryPrice->amount : (isset($data['paidAmount']) ? $data['paidAmount'] : $order->paidAmount);

        //Check if paid amount is full, so it can reflect here too
        if ((int) $order->isPaid) {
            $order->paidAmount  = 5550000;
            $order->save();
        }

        $order->save();

        return $order;
    }

    public function addRack($data, $uuid)
    {
        $order = Order::where('uuid', $uuid)->first(0);
        if (!$order) {
            throw new NotFoundException('No Order With That ID');
        };

        $order->store_rack = $data;

        $user = Customer::where('id', $order->customer_id)->first();

        $this->mailService->pickUpEmail([
            'to' => $user->email,
            'content' => [
                'user' => $user,
                'url' => tenant('organisation_url'),
                'logo' => tenant('organisation_logo'),
                'order' => $order,
                'companyName' => tenant('organisation_name')
            ]
        ]);

        $order->save();
    }

    public function getCustomerOrders($customerId = null, $status = null)
    {
        $orders = Order::with('customer', 'location', 'transactions.item.product')
            ->when($status, function ($query) use ($status, $customerId) {
                return $query->where(['customer_id' => $customerId, 'status' => $status]);
            }, function ($query) use ($customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->where("status", "!=", "deleted")
            ->orderBy('created_at', 'DESC')->get();

        $totalAmountForAllOrders = $orders->sum('bill');
        $totalPaidAmount = $orders->sum('paidAmount');
        $allPaid = Order::with('customer', 'location', 'transactions.item.product')
            ->when($status, function ($query) use ($status, $customerId) {
                return $query->where(['customer_id' => $customerId, 'is_paid' => true, 'status' => $status]);
            }, function ($query) use ($customerId) {
                return $query->where(['customer_id' => $customerId, 'is_paid' => true]);
            })
            ->orderBy('created_at', 'DESC')->get();
        $allUnPaid = Order::with('customer', 'location', 'transactions.item.product')
            ->when($status, function ($query) use ($status, $customerId) {
                return $query->where(['customer_id' => $customerId, 'is_paid' => false, 'status' => $status]);
            }, function ($query) use ($customerId) {
                return $query->where(['customer_id' => $customerId, 'is_paid' => false]);
            })
            ->orderBy('created_at', 'DESC')->get();

        return [
            "result" => count($orders),
            "totalAmount" => $totalAmountForAllOrders,
            "paidAmount" => $totalPaidAmount,
            "unpaidAmount" => (float)$totalAmountForAllOrders - (float)$totalPaidAmount,
            "allPaid" => $allPaid,
            "allUnpaid" => $allUnPaid,
            "order" => $orders
        ];
    }

    public function delete($data, $uuid)
    {
        $order = Order::where('uuid', $uuid)->first();
        if (!$order) {
            throw new NotFoundException('No order found');
        }

        if ($order->status == 'ready' || $order->status == 'completed' || $order->status == 'delivered') {
            throw new BadRequestException('Order is already ready or has been completed');
        }

        $walletPaymentSum = PaymentLog::where("order_id", $order->id)
            ->where("method_of_payment", "wallet")
            ->where("purpose", "payment")
            ->sum("amount");

        $customer = Customer::find($order->customer_id);

        $order->status = "deleted";
        $customer->wallet = (float)$customer->wallet + (float)$walletPaymentSum;
        $customer->save();
        $order->save();

        //Add to Deleted Records
        $deletedOrder = new DeletedOrder();
        $deletedOrder->order_id = $order->id;
        $deletedOrder->reason = $data['reason'];
        $deletedOrder->amount_refunded = $walletPaymentSum;
        $deletedOrder->save();
    }

    public function dashboard($startDate = null, $endDate = null, $location = null)
    {
        $orders = Order::when($startDate != $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        }, function ($query) use ($startDate) {
            return $query->whereDate('created_at', $startDate);
        })
            ->when($location, function ($query) use ($location) {
                return $query->where('location_id', $location);
            })
            ->get();

        $totalAmountForAllOrders = $orders->sum('bill');
        $totalPieces = $orders->sum('itemsCount');
        $totalPaidAmount = $orders->where('is_paid', true)->sum('bill');
        $totalUnpaidAmount = $orders->where('is_paid', false)->sum('bill');

        return [
            "result" => count($orders),
            "totalAmount" => $totalAmountForAllOrders,
            "paidAmount" => $totalPaidAmount,
            "unpaidAmount" => $totalUnpaidAmount,
            "totalPieces" => $totalPieces,
        ];
    }

    public function createPreOrder($data)
    {
        $preOrder = PreOrder::create([
            'customer_id' => $data['customerId'],
            'items_count' => $data['items_count']
        ]);

        $storeInfo = Location::where('id', $data["store_id"])->first();

        $previousOrder = Order::where("location_id", $data["store_id"])
            ->selectRaw('COUNT(*) as orderCount')->first();

        $order = Order::create([
            'pre_order_code' => $preOrder->id,
            'customer_id' => $preOrder->customer_id,
            'itemsCount' => $preOrder->items_count,
            'isExpress' => $data['isExpress'],
            'delivery_id' => $data['deliveryId'],
            'location_id' => $data['store_id'],
            'dateTimeIn' => Carbon::now(),
            'dateTimeOut' => $data['dateTimeOut'],
            'staff_id' => $data['staffId'],
            'paymentType' => $data['paymentType'],
            'status' => 'pre-order',
            'serial_number' => $storeInfo["store_code"] . '-' . ($previousOrder["orderCount"] + 1),
        ]);

        return [
            'preorder' => $preOrder,
            'order' => $order,
        ];
    }

    public function createInvoice($data)
    {
        return OrderInvoice::create($data);
    }

    public function editInvoice($data)
    {
        $orderInvoice = OrderInvoice::where('id', $data['id'])->first();
        if (!$orderInvoice) {
            throw new NotFoundException('No invoice found');
        }

        $orderInvoice->order_id = $data["orderID"] . "," . $orderInvoice["order_id"] ?? $orderInvoice["order_id"];
        $orderInvoice->total = $data["total"] ?? $orderInvoice['total'];
        $orderInvoice->save();

        return $orderInvoice;
    }

    public function deleteInvoice($data)
    {
        $orderInvoice = OrderInvoice::where('id', $data['id'])->first();
        $orderInvoice->delete();
    }

    public function allInvoice($status = null)
    {
        return OrderInvoice::when($status, function ($query) use ($status) {
            return $query->where('isPaid', $status);
        })
            ->get();
    }

    //Mark Order As Complete
    public function markAsPaid($data)
    {
        $orderInvoice = OrderInvoice::where('id', $data['id'])->first();
        $orderInvoice->isPaid = true;
        $orderInvoice->save();

        //Loop Through the Array And Update Each Order
        $orderIdsArray = explode(',', $orderInvoice['order_id']);
        foreach ($orderIdsArray as $orderId) {
            $cleanOrder = str_replace(' ', '', $orderId);
            $order = Order::where('uuid', $cleanOrder)->first();
            $order->status = 'delivered';
            $order->save();
        };

        return  [
            'orderInvoice' => $orderInvoice,
        ];
    }

    public function updateByScan($data)
    {
        $staff = auth()->user();
        $departmentId = $data["departmentId"];
        $orderId = $data["orderId"];
        $stage = $data["stage"];

        //Find Deparment
        $department = Department::where('id', $departmentId)->first();

        if ($department->name !== 'Driver' && $department->name !== 'store') {
            throw new BadRequestException("You are not permitted to access this function");
        };

        //Find Order
        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            throw new NotFoundException("Order not found");
        };

        if ($stage == 'scan-in' && $department->name == 'store') {
            $order->status = 'completed';
            $order->staff_marked_cleaned =
                $order->staff_marked_cleaned . ',' . $staff->fullName;
            $order->staff_collected_payment =
                $order->staff_staff_collected_payment . ',' . $staff->fullName;
        }

        if ($stage == 'scan-out' && $department->name == 'store') {
            $order->status = 'delivered';
            $order->dateCollected = Carbon::now();
        }

        $order->save();

        return $order;
    }

    public function createMovementList($data)
    {
        $movementList = MovementList::create([
            'driver_id' => $data['driverId'],
            'store_rep_id' => $data['storeId'],
            'location_id' => $data['locationId'],
            'order_ids' => $data['order_ids'],
            'total_bags' => $data['total_bags'],
        ]);

        //Retrive order id and mark as picked;
        $order_ids_array = explode(",", $data["order_ids"]);

        foreach ($order_ids_array as $order_id) {
            $order = Order::where('id', $order_id)->first();
            $order->isPicked = true;
            $order->save();
        }

        return $movementList;
    }

    public function getMovementList($per_page = 50, $start_date = null, $end_date = null)
    {
        $movementList = MovementList::whereBetween('movement_lists.created_at', [$start_date, $end_date])
            ->join("users as driver", "driver.id", "=", "movement_lists.driver_id")
            ->join("users as rep", "rep.id", "=", "movement_lists.store_rep_id")
            ->join("locations", 'locations.id', "=", "movement_lists.location_id")
            ->select(
                'movement_lists.*',
                'driver.fullName as driver_name',
                'rep.fullName as rep_name',
                'locations.locationName'
            )
            ->paginate($per_page);

        return $movementList;
    }

    public function getSingleMovementList($id)
    {
        $movementList = MovementList::where('movement_lists.id', $id)
            ->join("users as driver", "driver.id", "=", "movement_lists.driver_id")
            ->join("users as rep", "rep.id", "=", "movement_lists.store_rep_id")
            ->join("locations", 'locations.id', "=", "movement_lists.location_id")
            ->select(
                'movement_lists.*',
                'driver.fullName as driver_name',
                'rep.fullName as rep_name',
                'locations.locationName'
            )
            ->first();
        if (!$movementList) {
            throw new NotFoundException('No movement list was found');
        }

        return $movementList;
    }

    public function markOrderPaid($data)
    {
        $isWallet = (bool) $data["isWallet"];
        $staff_id = auth()->id();
        $store_id = $data["store_id"];
        $amount = intval($data["amount"]);
        $order = Order::where('id', $data["id"])->first();

        if (!$order) {
            throw new NotFoundException('Order not found');
        }

        $order->is_paid = true;

        if ($isWallet == true) {
            $customer = Customer::where("id", $order->customer_id)->first();
            //Check if wallet can cover payment
            $walletBal = intval($customer->wallet);

            if ((float)$amount > (float)$walletBal) {
                throw new BadRequestException('Amount greater than wallet balance');
            }

            $customer->wallet = $walletBal - $amount;
            $customer->save();
        }

        PaymentLog::create([
            "customer_id" => $order->customer_id,
            "order_id" => $order->id,
            "amount" => $amount,
            "method_of_payment" => $isWallet === true ? 'wallet' : $order->paymentType,
            "location_id" => $store_id,
            "purpose" => "payment",
            "user_id" => $staff_id,
        ]);

        $order->paidAmount =  $order->paidAmount + $amount;
        $order->status = 'delivered';
        $order->dateCollected = Carbon::now();
        $order->save();

        $user = Customer::where('id', $order->customer_id)->first();

        $this->mailService->completeNotificationEmail([
            'to' => $user->email,
            'content' => [
                'user' => $user,
                'url' => tenant('organisation_url'),
                'logo' => tenant('organisation_logo'),
                'order' => $order,
                'companyName' => tenant('organisation_name')
            ]
        ]);

        return $order;
    }

    public function clone($startDate, $endDate, $location = null)
    {
        $orders = Order::where("status", "!=", "deleted")
            ->when($location, function ($query) use ($location) {
                return $query->where("location_id", $location);
            })
            ->when($startDate != $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            }, function ($query) use ($startDate) {
                return $query->whereDate('created_at', $startDate);
            });

        $totalAmountForAllOrders = $orders->sum('bill');
        $totalPieces = $orders->sum('itemsCount');
        $totalPaidAmount = $orders->where('is_paid', true)->sum('bill');
        $totalUnpaidAmount = $orders->where('is_paid', false)->sum('bill');

        return [
            "totalAmount" => $totalAmountForAllOrders,
            "paidAmount" => $totalPaidAmount,
            "unpaidAmount" => $totalUnpaidAmount,
            "totalPieces" => $totalPieces,
        ];
    }

    public function createDiscountType($data)
    {
        return DiscountType::create($data);
    }

    public function editDiscountType($data)
    {
        $discountTypes = DiscountType::find($data['id']);
        if (!$discountTypes) {
            throw new NotFoundException('Discount type not found');
        }

        $discountTypes->update($data);

        return $discountTypes;
    }

    public function getDiscountTypes()
    {
        return DiscountType::all();
    }

    public function createCharge($data)
    {
        return Charges::create($data);
    }

    public function editCharge($data)
    {
        $charge = Charges::find($data['id']);
        if (!$charge) {
            throw new NotFoundException('Charge not found');
        }

        $charge->update($data);

        return $charge;
    }

    public function getCharges()
    {
        return Charges::all();
    }

    private function is_true($val, $return_null = false)
    {
        $boolval = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);
        return ($boolval === null && !$return_null ? false : $boolval);
    }

    private function calcTotalOrderTotalAndRevenue($orderItems, $isExpress)
    {
        $totalPrice = 0;
        $revenue = 0;

        if ($isExpress) {
            foreach ($orderItems as $item) {
                $charge = Charges::find($item['charge_id']);
                $totalPrice += (int) $item['expressPrice'] + (int) $charge->value;
            }
        } else {
            foreach ($orderItems as $item) {
                $charge = Charges::find($item['charge_id']);
                $totalPrice += (int) $item['price'] + (int) $charge->value;
            }
        }

        if ($isExpress) {
            foreach ($orderItems as $item) {
                $charge = Charges::find($item['charge_id']);
                $revenue += (int) $item['expressPrice'] + (int) $charge->value - (int) $item['cost_price'];
            }
        } else {
            foreach ($orderItems as $item) {
                $charge = Charges::find($item['charge_id']);
                $revenue += (int) $item['price'] + (int) $charge->value - (int) $item['cost_price'];
            }
        }

        return [$totalPrice, $revenue];
    }

    private function createItem($order_id, $order_item, $customerId, $order)
    {
        if ($order_item['tag_id']) {  //item has an existing tag
            $tag_id = $order_item["tag_id"];
            $item = Item::where('tagId', $tag_id)->first();

            if (!$item) {
                return [
                    "status" => "error",
                    "message" => "Item with tag id not found"
                ];
            }
        } else {
            //Item does not have a tag
            //Find The Product Option Count
            $product_option = ProductOption::where("id", $order_item["product_option_id"])->first();
            if ($product_option) {
                $pieceCount = $product_option->pieces ?? 1; // Assuming 'pieces' is the property that holds the number of pieces

                //Calulate the items count in each item
                $orderInOrder = Order::where('id', $order_id)->first();
                $orderInOrder->itemsCount = $orderInOrder->itemsCount +  intval($pieceCount);
                $orderInOrder->save();
                // Create an array to store the items
                $items = [];

                // Declare $item here to make it available in the entire block
                $item = new Item;
                $item->customer_id = $customerId;
                $item->product_id = $order_item['product_id'];
                $item->brand = $order_item["brand"];
                $item->description = $order_item["description"];
                $item->product_option_id = $order_item["product_option_id"];
                $item->notes = $order_item["notes"];
                $item->extra_info = $order_item["extra_info"];

                // Create items based on the piece count
                for ($i = 0; $i < $pieceCount; $i++) {
                    $newItem = clone $item;

                    // Generate a new tag ID for each iteration
                    $newItem->tagId = $this->generate->generateRandomString(6);
                    $newItem->save();

                    // Add the created item to the items array
                    $items[] = $newItem;
                }

                // Create transactions for each item
                foreach ($items as $index => $item) {
                    Transaction::create([
                        'item_id' => $item->id,
                        'order_id' => $order_id,
                        'product_id' => $order_item['product_id'],
                        'location_id' => $order->location_id,
                        'charge_id' => $order_item['charge_id'],
                        'price' => isset($order_item['isEdited']) && $this->is_true($order_item['isEdited']) ? $order_item['price'] : 0,
                        'status' => 'processing',
                        'number' => $index,
                    ]);
                }
            }
        }

        return ["status" => "success", "item" => $item];
    }
}
