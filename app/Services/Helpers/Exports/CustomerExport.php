<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $customers;


    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Full Name',
            'Paid Amount',
            'Total Bill'
        ];
    }

    public function collection()
    {
        return collect($this->customers)->map(function ($prop) {
            return [
                'Order ID' => $prop['id'],
                'Full Name' => $prop['full_name'],
                'Paid Amount' => $prop['total_paid_amount'],
                'Total Bill' => $prop['total_amount']
            ];
        });
    }
}
