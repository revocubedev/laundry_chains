<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePreOrderRequest extends FormRequest
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
            'customerId' => 'required|numeric|exists:customers,id',
            'items_count' => 'required|numeric',
            'store_id' => 'required|numeric|exists:locations,id',
            'isExpress' => 'required|boolean',
            'deliveryId' => 'required|numeric|exists:delivery_options,id',
            'dateTimeOut' => 'required',
            'staffId' => 'required|numeric|exists:users,id',
            'paymentType' => 'required|string',
        ];
    }
}
