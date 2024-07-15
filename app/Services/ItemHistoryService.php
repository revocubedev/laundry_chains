<?php

namespace App\Services;

use App\Models\ItemHistory;
use App\Exceptions\NotFoundException;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ItemHistoryService
{
    public function get_recent_history($transId)
    {
        $item_history = ItemHistory::leftJoin('departments', 'departments.id', '=', 'item_histories.department_id')
            ->leftJoin('users', 'users.id', '=', 'item_histories.staff_id')
            ->leftJoin('items', 'items.id', '=', 'item_histories.item_id')
            ->select("item_histories.*", "departments.name", "departments.scan_in_out", "users.fullName", "items.tagId")
            ->where('transaction_id', $transId)
            ->orderBy('item_histories.created_at', 'desc')
            ->get();
        if (!$item_history) {
            throw new NotFoundException("No item history with that transaction id");
        }

        return $item_history;
    }

    public function export($transId)
    {
        return Excel::download(new ReportsExport($transId), "reports.xlsx");
    }
}
