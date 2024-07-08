<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
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
            'location_name' => 'nullable',
            'address' => 'nullable',
            'phoneNumber' => 'nullable',
            'route_id' => 'nullable|exists:routes,id',
            'store_code' => 'nullable|unique:locations,store_code',
        ];
    }
}
