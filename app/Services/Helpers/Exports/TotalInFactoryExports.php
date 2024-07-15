<?php

namespace App\Services\Helpers\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Session;

class TotalInFactoryExports implements FromArray, WithHeadings
{


    public function array(): array
    {
    }



    public function headings(): array
    {
    }
}
