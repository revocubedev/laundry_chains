<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
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
            'email' => 'nullable|email|unique:users',
            'password' => 'nullable|min:6',
            'phone_number' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'location_id' => 'nullable|exists:locations,id',
            'permissions' => 'nullable|array',
            'role' => 'nullable|string',
        ];
    }
}
