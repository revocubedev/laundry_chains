<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Order;
use App\Models\Outbound;
use App\Models\OutboundIteration;
use App\Models\Transaction;
use Carbon\Carbon;

class OutboundService
{
    public function handleOrderItems($item_id)
    {
        $transaction = Transaction::where("item_id", $item_id)
            ->latest()
            ->first();

        if (!$transaction) {
            throw new NotFoundException("Transaction not found");
        }

        $order = Order::find($transaction->order_id);
        if (!$order) {
            throw new NotFoundException("Order not found");
        }

        $itemCount = $order->itemsCount;

        // Check If Outbound Iteration Has Started
        $old_iteration = OutboundIteration::where("order_id", $order->id)->first();

        if ($old_iteration) {
            $outboundCount = $old_iteration->item_count;
            $old_iteration->item_count = $outboundCount + 1;
            $old_iteration->save();

            if ($old_iteration->item_count >= $itemCount) {
                // Update Order
                $order->status = 'ready';
                $order->dateCompleted = Carbon::now();
                $order->save();

                return "Items in this order is complete";
            }

            return "Items in this order is not yet complete {$old_iteration->item_count}/{$itemCount}";
        }

        // Create New Outbound Iteration
        OutboundIteration::create([
            "order_id" => $order->id,
            "item_count" => 1
        ]);

        return "Items in this order is not yet complete 1/{$order->itemsCount}";
    }
}
