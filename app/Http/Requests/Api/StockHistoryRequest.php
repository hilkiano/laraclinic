<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StockHistoryRequest extends FormRequest
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
            'stock_id' => 'required|exists:stocks,id',
            'type' => 'required|string',
            'quantity' => 'required|integer',
            'description' => 'nullable|string',
            'transaction_id' => 'nullable|string',
        ];
    }
}
