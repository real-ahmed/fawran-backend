<?php

namespace App\Http\Requests\V1\Vendor\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_item_id' => ['required', 'integer', 'exists:vendor_items,id'],
            'adjustment' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
