<?php

namespace App\Services\Helpers\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WalletFunding implements FromCollection, WithHeadings, ShouldAutoSize
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
            'Customer',
            'Amount',
            'Store',
            'Date of Payment',
            'Staff',
        ];
    }

    public function collection()
    {
        return collect($this->data)->map(function ($prop) {
            return [
                'Customer' => $prop['full_name'],
                'Amount' => $prop['amount'],
                'Store' => $prop['store'],
                'Date of Payment' => Carbon::parse($prop['created_at'])->format('Y-m-d'),
                'Staff' => $prop['staff'],
            ];
        });
    }
}
