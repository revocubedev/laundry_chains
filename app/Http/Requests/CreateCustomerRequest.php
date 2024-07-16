<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
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
            'full_name' => 'required|not_in:""|string',
            'phone' => 'required|unique:customers',
            'email' => 'required|email|unique:customers',
            'secondary_phone' => 'nullable',
            'street_address' => 'nullable',
            'city' => 'nullable',
            'gender' => 'nullable',
            'birthday' => 'nullable',
            'shirt_preference' => 'nullable',
            'trouser_preference' => 'nullable',
            'starch' => 'nullable',
            'private_notes' => 'nullable',
            'default_payment' => 'nullable',
            'discount' => 'nullable',
        ];
    }
}
