<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Transaction;

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
}
