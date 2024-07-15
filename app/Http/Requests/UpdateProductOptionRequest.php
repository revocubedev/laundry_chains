<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductOptionRequest extends FormRequest
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
            'product_id' => 'nullable|exists:products,id',
            'option_name' => 'nullable|string',
            'price' => 'nullable|numeric',
            'expressPrice' => 'nullable|numeric',
            'cost_price' => 'nullable|numeric',
            'pieces' => 'nullable|numeric'
        ];
    }
}
