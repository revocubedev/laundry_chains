<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
            'customer_id' => 'required|integer|exists:customers,id',
            'store_id' => 'required|integer|exists:locations,id',
            'staffId' => 'required|integer|exists:users,id',
            'total_amount' => 'required',
            'note' => 'nullable|string',
            'discount' => 'nullable',
            'isExpress' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'paidAmount' => 'nullable',
            'order_items' => 'required|array',
            'extra_info' => 'nullable|string',
            'revenue' => 'nullable',
            'paymentType' => 'nullable|string',
            'deliveryId' => 'nullable|integer|exists:delivery_options,id',
            'dateTimeOut' => 'nullable',
            'discount_percentage' => 'nullable',
            'extra_discount_percentage' => 'nullable',
            'extra_discount_value' => 'nullable',
            'summary' => 'nullable',
        ];
    }
}
