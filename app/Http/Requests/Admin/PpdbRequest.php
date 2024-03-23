<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PpdbRequest extends FormRequest
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
            'ppdb_type_id' => 'required|exists:ppdb_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'capacity' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'required|boolean'
        ];
    }
}
