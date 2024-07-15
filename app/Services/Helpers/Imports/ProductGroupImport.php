<?php

namespace App\Services\Helpers\Imports;

use Illuminate\Support\Str;
use App\Models\ProductOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProductGroupImport implements ToModel, withHeadingRow //Import Product Options
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        $uuid = Str::uuid()->toString();
        return new ProductOption([
            'product_id' => $row['product_id'],
            'option_name' => $row['option_name'],
            'price' => $row['price'],
            'expressPrice' => $row['expressprice'],
            'isExpress' => $row['isexpress'],
            'cost_price' => $row['cost_price'],
            'pieces' => $row['pieces'],
        ]);
    }
}
