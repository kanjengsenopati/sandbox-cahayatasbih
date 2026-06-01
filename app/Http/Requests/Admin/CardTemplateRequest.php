<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CardTemplateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:student_card,exam_card',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'exam_bill_requirements' => 'nullable|array',
            'exam_bill_requirements.*' => 'exists:bill_types,id',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama template wajib diisi',
            'type.required' => 'Tipe template wajib dipilih',
            'type.in' => 'Tipe template tidak valid',
            'academic_year_id.exists' => 'Tahun ajaran terpilih tidak valid',
            'exam_bill_requirements.array' => 'Format persyaratan tagihan tidak valid',
            'exam_bill_requirements.*.exists' => 'Jenis tagihan terpilih tidak valid',
        ];
    }
}
