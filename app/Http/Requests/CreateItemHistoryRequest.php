<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateItemHistoryRequest extends FormRequest
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
            'item_id' => 'required|exists:items,id',
            'department_id' => 'required|exists:departments,id',
            'staff_id' => 'required|exists:users,id',
            'stage' => 'required|string',
            'extra_info' => 'nullable|string',
            'from' => 'required|exists:departments,id',
            'product_id' => 'required|exists:products,id',
        ];
    }
}
