<?php

namespace App\Http\Requests\V1\Vendor\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StorePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'bank_details' => ['required', 'string', 'max:1000'],
        ];
    }
}
