<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:voucher,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'for_all_products' => 'boolean',
        ];
    }
}
