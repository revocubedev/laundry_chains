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
use App\Models\Item;
use App\Models\PaymentLog;
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

    public function getByOrderId($id)
    {
        $order = Order::with(
            'customer',
            'location',
            'transactions.charge',
            'transactions.item.product',
            'transactions.item.product_option',
            'user'
        )
            ->where("id", $id)
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

        [$totalPrice, $revenue] = $this->calcTotalOrderTotalAndRevenue($data['orderItems'], $data['isExpress']);

        $customerDiscount = isset($data['discount_percentage']) ? $totalPrice * ($data['discount_percentage'] / 100) : 0;

        $data['revenue'] = $revenue;

        $discountAmount = $totalPrice - $customerDiscount;

        $data['discount'] = $customerDiscount;

        $data['paidAmount'] = (bool) $data['is_paid'] ? $discountAmount * $extraDiscount + (float) $deliveryPrice->amount : $data['paidAmount'];

        $data['vat'] = (7.5 * 100) / $totalPrice;

        $data['serial_number'] = $storeInfo["store_code"] . '-' . ($previousOrder + 1);

        $data['extra_discount_percentage'] = $extraDiscount;

        $data['extra_discount_value'] = $discountAmount * $extraDiscount;

        $data['dateTimeIn'] = Carbon::now();

        $data['bill'] = $totalPrice + (float) $deliveryPrice->amount;

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
                'logo' => tenant(''),
                'order' => $order
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

    public function addRack($data, $id)
    {
        $newData = $data;

        $order = Order::find($id);
        if (!$order) { //Throw Error IF Order Is Not Found
            throw new NotFoundException('No Order With That ID');
        };

        $order->store_rack = $newData;

        $user = Customer::where('id', $order->customer_id)->first();

        $this->mailService->pickUpEmail([
            'to' => $user->email,
            'content' => [
                'user' => $user,
                'url' => tenant('organisation_url'),
                'logo' => tenant(''),
                'order' => $order
            ]
        ]);

        $order->save();
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
                $charge = Charges::find($orderItems['charge_id']);
                $totalPrice += (int) $item['expressPrice'] + (int) $charge->value;
            }
        } else {
            foreach ($orderItems as $item) {
                $charge = Charges::find($orderItems['charge_id']);
                $totalPrice += (int) $item['price'] + (int) $charge->value;
            }
        }

        if ($isExpress) {
            foreach ($orderItems as $item) {
                $charge = Charges::find($orderItems['charge_id']);
                $revenue += (int) $item['expressPrice'] + (int) $charge->value - (int) $item['cost_price'];
            }
        } else {
            foreach ($orderItems as $item) {
                $charge = Charges::find($orderItems['charge_id']);
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
                    "status" => "error", "message" => "Item with tag id not found"
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
                        'price' => isset($order_item['isEdited']) && $this->is_true($order_item['isEdited'])  ? $order_item['price'] : null,
                        'status' => 'processing',
                        'number' => $index,
                    ]);
                }
            }
        }

        return ["status" => "success", "item" => $item];
    }
}
