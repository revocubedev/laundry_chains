<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'full_name' => 'nullable|string',
            'phone' => 'nullable|unique:customers',
            'email' => 'nullable|email|unique:customers',
            'secondary_phone' => 'nullable',
            'street_address' => 'nullable',
            'city' => 'nullable',
            'gender' => 'nullable',
            'birthday' => 'nullable',
            'shirt_preference' => 'nullable',
            'starch' => 'nullable',
            'private_notes' => 'nullable',
            'default_payment' => 'nullable',
            'discount' => 'nullable',
        ];
    }
}
