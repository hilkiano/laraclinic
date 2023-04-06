<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'label' => 'required|string|max:255',
            'buy_price' => 'nullable|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'id' => 'nullable|integer|min:1'
        ];
    }
}
