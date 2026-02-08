<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.addons' => ['nullable', 'array'],
            'items.*.addons.*' => ['integer', 'exists:addons,id'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['nullable', 'in:none,amount,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'service' => ['nullable', 'numeric', 'min:0'],
            'open_bill_id' => ['nullable', 'integer', 'exists:orders,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
