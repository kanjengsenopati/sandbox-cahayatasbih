<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentGraduationRequest extends FormRequest
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
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['required', 'exists:students,id'],
            'graduation_option' => ['nullable', 'in:lanjut_studi,keluar'],
            'next_action' => ['nullable', 'in:lanjut_studi,lanjut_pondok,keluar'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_ids.required' => 'Silakan pilih setidaknya satu siswa untuk diproses kelulusannya.',
            'student_ids.array' => 'Data siswa terpilih tidak valid.',
            'student_ids.*.exists' => 'Salah satu siswa yang dipilih tidak ditemukan di database.',
        ];
    }
}
