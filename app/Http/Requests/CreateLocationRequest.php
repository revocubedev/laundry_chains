<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLocationRequest extends FormRequest
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
            'locationName' => 'required',
            'address' => 'required',
            'phoneNumber' => 'required',
            'route_id' => 'required|exists:routes,id',
            'store_code' => 'required|unique:locations,store_code',
        ];
    }
}
