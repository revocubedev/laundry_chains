<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchScanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'to' => 'required|exists:departments,id',
            'from' => 'required|exists:departments,id',
            'staffId' => 'required|exists:users,id',
            'stage' => 'required|string',
            'departmentID' => 'required|exists:departments,id',
        ];
    }
}
