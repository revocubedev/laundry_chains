<?php

namespace App\Services\Helpers\Imports;

use Illuminate\Support\Str;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, withHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected $generat;


    public function model(array $row)
    {
        $uuid = Str::uuid()->toString();
        return new Customer([
            'uuid' => $uuid,
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'street_address' => $row['street_address'],
            'credit' => $row['credit'],
            'notes' => $row['notes'],
            'private_notes' => $row['private_notes'],
            'discount' => floatval($row['discount']),
            "clean_cloud_id" => $row['clean_cloud_id']
        ]);
    }
}
