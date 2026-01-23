<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AcademicYearRequest extends FormRequest
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
        return match ($this->method()) {
            'POST' => [
                'name' => ['required', 'string', 'unique:academic_years', 'max:255'],
                'start_year' => ['required', 'numeric', 'digits:4'],
                'end_year' => ['required', 'numeric', 'digits:4'],
            ],
            'PUT', 'PATCH' => [
                'name' => ['required', 'string', 'max:255', 'unique:academic_years,name,' . $this->academic_year->id],
                'start_year' => ['required', 'numeric', 'digits:4'],
                'end_year' => ['required', 'numeric', 'digits:4'],
            ],
            default => [],
        };
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tahun Akademik tidak boleh kosong',
            'name.string' => 'Tahun Akademik harus berupa string',
            'name.unique' => 'Tahun Akademik sudah ada',
            'name.max' => 'Tahun Akademik maksimal 255 karakter',
            'start_year.required' => 'Tahun Mulai tidak boleh kosong',
            'start_year.numeric' => 'Tahun Mulai harus berupa angka',
            'start_year.digits' => 'Tahun Mulai harus 4 digit',
            'end_year.required' => 'Tahun Selesai tidak boleh kosong',
            'end_year.numeric' => 'Tahun Selesai harus berupa angka',
            'end_year.digits' => 'Tahun Selesai harus 4 digit',
        ];
    }
}
