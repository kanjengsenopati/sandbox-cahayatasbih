<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OutletRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route('outlet') ? $this->route('outlet')->id : null;
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:outlets,code,' . $id,
            'address' => 'nullable|string',
            'is_active' => 'required|boolean',
        ];
    }
}
