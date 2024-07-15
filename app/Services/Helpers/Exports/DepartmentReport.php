<?php

namespace App\Services\Helpers\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DepartmentReport implements FromCollection, WithHeadings, ShouldAutoSize
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
            'Tag number',
            'Order ID',
            'Item',
            'Type',
            'Brand',
            'Color',
            'Scanned In By',
            'Scan In Time',
            'Scanned Out By',
            'Sent To',
            'Sent On',
            'Status',
            'Collection Date'
        ];
    }

    public function collection()
    {
        return collect($this->parameter_one)->map(function ($prop) {
            return [
                'Tag number' => $prop["scannedIn"] ? $prop["scannedIn"]['tagId'] : $prop["scannedOut"]['tagId'],
                'Order ID' => $prop["scannedIn"] ? $prop["scannedIn"]['serial_number'] : $prop["scannedOut"]['serial_number'],
                'Item' => $prop["scannedIn"] ? $prop["scannedIn"]['name'] : $prop["scannedOut"]['name'],
                'Type' => $prop["scannedIn"] ? $prop["scannedIn"]['option_name'] : $prop["scannedOut"]['option_name'],
                'Brand' => $prop["scannedIn"] ? $prop["scannedIn"]['brand'] : $prop["scannedOut"]['brand'],
                'Color' => $prop["scannedIn"] ? json_decode($prop["scannedIn"]['extra_info'], true)["color"] : json_decode($prop["scannedOut"]['extra_info'], true)["color"],
                'Scanned In By' => $prop["scannedIn"] ? $prop["scannedIn"]['fullName'] : "-",
                'Scan In Time' => $prop["scannedIn"] ? Carbon::parse($prop["scannedIn"]['created_at'])->format('Y-m-d H:i:s') : "-",
                'Scanned Out By' => $prop["scannedOut"] ? $prop["scannedOut"]['fullName'] : "-",
                'Sent To' => $prop["scannedOut"] ? $prop["scannedOut"]['sent_to_name'] : $prop["scannedIn"]['sent_to_name'],
                'Sent On' => $prop["scannedOut"] ? Carbon::parse($prop["scannedOut"]['created_at'])->format('Y-m-d H:i:s') : "-",
                'Status' => $prop["scannedOut"] ? "scanned-out" : "scanned-in",
                'Collection Date' => $prop["scannedOut"] ? Carbon::parse($prop["scannedOut"]['dateTimeOut'])->format('Y-m-d') : Carbon::parse($prop["scannedIn"]['dateTimeOut'])->format('Y-m-d')
            ];
        });
    }
}
