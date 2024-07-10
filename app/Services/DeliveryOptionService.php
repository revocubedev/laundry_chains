<?php

namespace App\Services;

use App\Models\DeliveryOptions;
use App\Models\OrderOption;

use App\Exceptions\NotFoundException;

class DeliveryOptionService
{
    public function create($data)
    {
        return DeliveryOptions::create($data);
    }

    public function edit($data, $uuid)
    {
        $deliveryOption = DeliveryOptions::where('uuid', $uuid)->first();
        if (!$deliveryOption) {
            throw new NotFoundException('Delivery option not found');
        }

        $deliveryOption->update($data);

        return $deliveryOption;
    }

    public function getAll()
    {
        return DeliveryOptions::all();
    }

    public function getOne($uuid)
    {
        $deliveryOption = DeliveryOptions::where('uuid', $uuid)->first();
        if (!$deliveryOption) {
            throw new NotFoundException('Delivery option not found');
        }

        return $deliveryOption;
    }

    public function delete($uuid)
    {
        $deliveryOption = DeliveryOptions::where('uuid', $uuid)->first();
        if (!$deliveryOption) {
            throw new NotFoundException('Delivery option not found');
        }

        $deliveryOption->delete();
    }

    public function createOrderOption($data)
    {
        return OrderOption::create([
            'number_of_days' => $data['days'],
            'max_order_item' => $data['max_order_item']
        ]);
    }

    public function getOrderOption()
    {
        $orderOption = OrderOption::find(1);
        if (!$orderOption) {
            throw new NotFoundException('Order option not found');
        }

        return $orderOption;
    }

    public function editOrderOption($data)
    {
        $orderOption = OrderOption::find(1);
        if (!$orderOption) {
            throw new NotFoundException('Order option not found');
        }

        $orderOption->update([
            'number_of_days' => $data['days'] ?? $orderOption->number_of_days,
            'max_order_item' => $data['max_order_item'] ?? $orderOption->max_order_item
        ]);

        return $orderOption;
    }
}
