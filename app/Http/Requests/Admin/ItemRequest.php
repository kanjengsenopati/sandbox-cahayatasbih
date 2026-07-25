<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $cleanPrice = $this->price !== null ? (int) preg_replace('/\D/', '', (string) $this->price) : 0;
        $cleanSellingPrice = $this->selling_price !== null ? (int) preg_replace('/\D/', '', (string) $this->selling_price) : 0;
        $profit = max(0, $cleanSellingPrice - $cleanPrice);

        $this->merge([
            'price' => $cleanPrice,
            'selling_price' => $cleanSellingPrice,
            'profit' => $profit,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_item_id' => 'required|exists:category_items,id',
            'price' => 'required|numeric',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'code' => 'required|string|max:255',
            'selling_price' => 'required|numeric',
            'profit' => 'nullable|numeric',
            'outlet_id' => auth()->user()->outlet_id ? 'nullable|exists:outlets,id' : 'required|exists:outlets,id',
        ];
    }
}
