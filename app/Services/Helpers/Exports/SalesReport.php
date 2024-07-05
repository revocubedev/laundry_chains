<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\ItemHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $parameter_one;
    //protected $parameter_two;

    public function __construct($parameter_one)
    {
        $this->parameter_one = $parameter_one;
        //$this->parameter_two = $parameter_two;
    }

    public function headings(): array
    {
        return [
            'Store',
            'Order ID',
            'Placed',
            'Staff Taking Order',
            'Ready date',
            'Summary',
            'Staff Marking Cleaned',
            'Collected Date',
            'Staff Completing',
            'Date Cleaned',
            'Customer',
            'Email',
            'Phone',
            'Address',
            'Pieces',
            'Paid',
            'Payment Type',
            'Staff Taking Payment',
            'Discount',
            'Credit',
            'Total',
            'Status',
        ];
    }
    public function collection()
    {
        return collect($this->parameter_one)->map(function ($prop) {
            return [
                'Store' => $prop['store_code'],
                'Order ID' => $prop['serial_number'],
                'Placed' => $prop['dateTimeIn'],
                'Staff Taking Order' => $prop['fullName'],
                'Ready date' => Carbon::parse($prop['dateTimeOut'])->format('Y-m-d'),
                'Summary' => $prop['summary'],
                'Staff Marking Cleaned' => $prop['staff_marked_cleaned'],
                'Collected Date' => $prop['dateCollected'] ? Carbon::parse($prop['dateCollected']) : 'Not Collected',
                'Staff Completing' => $prop['fullName'],
                'Date Cleaned' => $prop['dateCompleted'],
                'Customer' => $prop['full_name'],
                'Email' => $prop['email'],
                'Phone' => $prop['phone'],
                'Address' => $prop['street_address'],
                'Pieces' => $prop['itemsCount'],
                'Paid' => (int) $prop['is_paid'] ? "Yes" : "No",
                'Payment Type' => $prop['paymentType'],
                'Staff Taking Payment' => $prop['staff_collected_payment'],
                'Discount' => $prop['discount'],
                'Credit' => '0',
                'Total' => $prop['bill'],
                'Status' => $prop['status'],
            ];
        });
    }
}
