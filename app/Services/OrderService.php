<?php

namespace App\Services;

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
}
