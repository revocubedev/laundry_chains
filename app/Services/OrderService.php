<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Charges;
use App\Models\DiscountType;
use App\Models\Order;

class OrderService
{
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
}
