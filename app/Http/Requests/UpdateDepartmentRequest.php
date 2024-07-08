<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
            'name' => 'nullable|string',
            'action_options' => 'nullable',
            'default_options' => 'nullable',
            'scan_in_out' => 'nullable|boolean',
            'allow_batch_job' => 'nullable|boolean',
        ];
    }
}
