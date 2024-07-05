<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
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
            'email' => 'required|email|unique:tenants,email',
            'full_name' => 'required|string',
            'organisation_name' => 'required|string',
            'organisation_email' => 'required|email|unique:tenants,organisation_email',
            'password' => 'required|string',
        ];
    }
}
