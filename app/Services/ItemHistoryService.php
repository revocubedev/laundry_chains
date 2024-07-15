<?php

namespace App\Services;

use App\Models\ItemHistory;
use App\Exceptions\NotFoundException;
use App\Exceptions\BadRequestException;
use App\Models\Department;
use App\Models\Item;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Helpers\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ItemHistoryService
{
    private function scanIn($item, $to, $from, $staffId, $stage, $departmentID, $staff)
    {
        $itemIn = Item::where('tagId', $item['item_id'])->first();
        if (!$itemIn) {
            throw new NotFoundException("Item not found");
        }

        $departmentId = Department::where('name', $to)->first();
        if (!$departmentId) {
            throw new NotFoundException("Department not found");
        }

        $transactionId = Transaction::where("item_id", $item['item_id'])->orderBy('created_at', 'desc')->first();
        if (!$transactionId) {
            throw new NotFoundException("Transaction not found");
        }

        $oldHistory = ItemHistory::where("item_id", $item['item_id'])->latest()->first();
        $currentDepartment = Department::find($oldHistory->department_id ?? 1);

        if (
            $oldHistory->stage === "scan-in" && $stage === "scan-in" &&
            $oldHistory->department_id == $departmentID
        ) {
            return $itemIn->tagId . '(You cannot scan-in twice)';
        }

        if (
            $oldHistory->stage === "scan-out" && $stage === "scan-out" &&
            $oldHistory->department_id == $departmentID
        ) {
            return $itemIn->tagId . '(You cannot scan-out twice)';
        }

        if ($oldHistory->department_id == 12) {
            return $itemIn->tagId . '(Item has left the factory process)';
        }

        if ($oldHistory->staff_id != $staffId && $oldHistory->department_id == $departmentID) {
            return $itemIn->tagId . '(You are not the staff that scan in this item)';
        }

        if (
            $oldHistory->stage == 'scan-in' &&
            $stage === "scan-in" && $currentDepartment->scan_in_out != false
        ) {
            return $itemIn->tagId . '(Item has not been scan-out by the previous department)';
        }

        if ($stage == 'scan-out' && $oldHistory->sending_to != $departmentId->id) {
            return $itemIn->tagId . '(Item is not meant for your department)';
        }

        if ($stage == 'scan-in' && $oldHistory->sending_to != $departmentID) {
            return $itemIn->tagId . '(Item is not meant for your department)';
        }

        $userDepartment = Department::find($departmentID);
        if (!$userDepartment) {
            throw new NotFoundException("Department not found");
        }

        $order = Order::find($transactionId->order_id);

        if ($userDepartment->name === 'store' && $stage === "scan-out") {
            $order->status = 'delivered';
            $order->save();
        }

        if ($userDepartment->name === 'store' && $stage === "scan-in") {
            $order->status = 'completed';
            $order->staff_marked_cleaned =  $order->staff_marked_cleaned . ',' . $staff->fullName;
            $order->staff_collected_payment = $order->staff_collected_payment . ',' . $staff->fullName;
            $order->save();
        }

        ItemHistory::create([
            'item_id' => $itemIn->id,
            'transaction_id' => $transactionId->id,
            'department_id' => $departmentId->id,
            'staff_id' => $staffId,
            'order_id' => $transactionId->order_id,
            'stage' => $stage,
            'sending_to' => $departmentId->id,
            'sent_to_name' => $to,
            'from' => $from,
            'status' => $stage,
            'product_id' => $itemIn->product_id,
        ]);

        return null;
    }

    public function create_history($data)
    {
        $departmentId = Department::where('name', $data['sending_to'])->first();
        if (!$departmentId) {
            throw new NotFoundException("Department not found");
        }

        $transactionId = Transaction::where("item_id", $data['item_id'])->orderBy('created_at', 'desc')->first();
        if (!$transactionId) {
            throw new NotFoundException("Transaction not found");
        }

        $oldHistory = ItemHistory::where("item_id", $data['item_id'])->latest()->first();
        $currentDepartment = Department::find($oldHistory->department_id ?? 1);
        if (!$currentDepartment) {
            throw new NotFoundException("Department not found");
        }

        $staff = auth()->user();

        $data['sending_to'] = $departmentId->id;
        $data['order_id'] = $transactionId->order_id;
        $data['transaction_id'] = $transactionId->id;

        if ($oldHistory) {
            if ($oldHistory->stage === "scan-in" && $data['stage'] === "scan-in" && $oldHistory->department_id == $data['department_id']) {
                throw new BadRequestException("You cannot scan-in twice");
            }

            if ($oldHistory->stage === "scan-out" && $data['stage'] === "scan-out" && $oldHistory->department_id == $data['department_id']) {
                throw new BadRequestException("You cannot scan-out twice");
            }

            if ($oldHistory->department_id == 12) {
                throw new BadRequestException("Item has left the factory process");
            }

            if ($oldHistory->staff_id != $data['user_id'] && $oldHistory->department_id == $data['department_id']) {
                throw new BadRequestException("You are not the staff that scan in this item");
            }

            if (
                $oldHistory->stage == 'scan-in' &&
                $data['stage'] === "scan-in" && $currentDepartment->scan_in_out != false
            ) {
                throw new BadRequestException("Item has not been scan-out by the previous department");
            }

            if ($data['stage'] == 'scan-out' && $oldHistory->sending_to != $departmentId->id) {
                throw new BadRequestException("Item is not meant for your department");
            }

            if ($data['stage'] == 'scan-in' && $oldHistory->sending_to != $data['department_id']) {
                throw new BadRequestException("Item is not meant for your department");
            }
        }

        $userDepartment = Department::find($data['department_id']);
        $order = Order::find($transactionId->order_id);

        if ($userDepartment->name === 'store' && $data['stage'] === "scan-out") {
            $order->status = 'delivered';
            $order->save();
        }

        if ($userDepartment->name === 'store' && $data['stage'] === "scan-in") {
            $order->status = 'completed';
            $order->staff_marked_cleaned =  $order->staff_marked_cleaned . ',' . $staff->fullName;
            $order->staff_collected_payment = $order->staff_collected_payment . ',' . $staff->fullName;
            $order->save();
        }

        return ItemHistory::create($data);
    }

    public function batchScan($data)
    {
        $staff = auth()->user();
        $batchBox = $data['batch'];
        $responses = [];

        foreach ($batchBox as $batchItem) {
            $response = $this->scanIn($batchItem, $data['to'], $data['from'], $data['staffId'], $data['stage'], $data['department_id'], $staff);
            if ($response) {
                $responses[] = $response;
            }
        }

        return $responses;
    }

    public function get_all_history($transaction_id, $department_id, $user_id)
    {
        return ItemHistory::join('departments', 'departments.id', '=', 'item_histories.department_id')
            ->join('users', 'users.id', '=', 'item_histories.staff_id')
            ->where('item_histories.transaction_id', '=', $transaction_id)
            ->where('item_histories.department_id', '=', $department_id)
            ->where('item_histories.staff_id', '=', $user_id)
            ->select('item_histories.*', 'departments.name as department_name', 'users.fullName as user_name')
            ->get();
    }

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
