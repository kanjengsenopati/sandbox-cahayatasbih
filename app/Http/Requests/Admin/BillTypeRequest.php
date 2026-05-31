<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BillTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:MONTHLY,OTHER',
            'bill_item_id' => 'required|exists:bill_items,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ];
    }

    /**
     * Merge name_select or name_custom into the name field before validation
     */
    protected function prepareForValidation()
    {
        $name = $this->name_select === 'Lainnya' ? $this->name_custom : $this->name_select;
        $this->merge([
            'name' => $name,
        ]);
    }
}
