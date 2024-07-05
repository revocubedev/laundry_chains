<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\ItemHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class ReportsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }
    public function collection()
    {
        $item_history = ItemHistory::leftJoin('departments', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('users', 'users.id', '=', 'item_histories.staff_id')
            ->leftJoin('items', 'items.id', '=', 'item_histories.item_id')
            ->select("item_histories.*", "departments.name", "departments.scan_in_out", "users.fullName", "items.tagId")
            ->where('transaction_id', $this->parameter)
            ->orderBy('item_histories.created_at', 'desc')->get();

        return $item_history;
    }
}


class ReportsExportTwo implements FromCollection, WithHeadings
{
    protected $data;
    protected $staffName;
    protected $departmentName;
    protected $sendingTo;

    public function __construct($data, $staffName, $departmentName, $sendingTo)
    {
        $this->data = $data;
        $this->staffName = $staffName;
        $this->departmentName = $departmentName;
        $this->sendingTo = $sendingTo;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                'ID' => $item['id'],
                'Item ID' => $item['item_id'],
                'Transaction ID' => $item['transaction_id'],
                'Order ID' => $item['order_id'],
                'Staff Name' => $this->staffName,
                'Department Name' => $this->departmentName,
                'Stage' => $item['stage'],
                'Created At' => $item['created_at'],
                'Sent To' => $this->sendingTo,
                'Extra Info' => $item['extra_info'],
                'UUID' => $item['uuid'],
                'Is Loop' => $item['is_loop'],
                'Scan In/Out' => $item['scan_in_out'],
            ];
        });
    }

    public function headings(): array
    {
        // Define column headings
        return [
            'ID',
            'Item ID',
            'Transaction ID',
            'Order ID',
            'Staff Name',
            'Department Name',
            'Stage',
            'Created At',
            'Sent To',
            'Extra Info',
            'UUID',
            'Is Loop',
            'Scan In/Out',
        ];
    }
}
