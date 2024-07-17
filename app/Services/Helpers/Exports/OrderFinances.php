<?php

namespace App\Services\Helpers\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderFinances implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data;


    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Order',
            'Customer',
            'Amount',
            'Order Bill',
            'Store',
            'Method',
            'Date of Payment',
            'Staff',
        ];
    }

    public function collection()
    {
        return collect($this->data)->map(function ($prop) {
            return [
                'Order' => $prop['serial_number'],
                'Customer' => $prop['full_name'],
                'Amount' => $prop['amount'],
                'Order Bill' => $prop['bill'],
                'Store' => $prop['store'],
                'Method' => $prop['method_of_payment'],
                'Date of Payment' => Carbon::parse($prop['created_at'])->format('Y-m-d'),
                'Staff' => $prop['staff'],
            ];
        });
    }
}
