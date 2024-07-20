<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'customer_id' => 'nullable|integer|exists:customers,id',
            'store_id' => 'nullable|integer|exists:locations,id',
            'staffId' => 'nullable|integer|exists:users,id',
            'note' => 'nullable|string',
            'isExpress' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'paidAmount' => 'nullable',
            'paymentType' => 'nullable|string',
            'delivery_id' => 'nullable|integer|exists:delivery_options,id',
            'dateTimeOut' => 'nullable',
        ];
    }
}
