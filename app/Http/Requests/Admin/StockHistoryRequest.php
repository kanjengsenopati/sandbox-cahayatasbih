<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:IN,OUT,ADJUSTMENT'],
            'notes' => ['nullable', 'string', 'max:255'],
            'outlet_id' => auth()->user()->outlet_id ? ['nullable', 'exists:outlets,id'] : ['nullable', 'exists:outlets,id'],
        ];
    }
}
