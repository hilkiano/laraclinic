<?php

namespace App\Http\Requests\Api\OnlineTransaction;

use Illuminate\Foundation\Http\FormRequest;

class FindPatientRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'          => 'nullable|sometimes',
            'phone_number'  => 'nullable|sometimes',
            'address'       => 'nullable|sometimes',
            'record_no'     => 'nullable|sometimes'
        ];
    }
}
