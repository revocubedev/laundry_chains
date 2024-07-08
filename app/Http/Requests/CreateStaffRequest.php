<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
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
            'fullName' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phoneNumber' => 'nullable',
            'department_id' => 'required|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'location_id' => 'nullable|exists:locations,id',
        ];
    }
}
