<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GradePromotionRequest extends FormRequest
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
            'migration_type' => ['required', 'in:transfer,promotion'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'uuid', 'exists:students,id'],
            'new_classroom_id' => ['required', 'uuid', 'exists:classrooms,id'],
            'academic_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'migration_type.required' => 'Jenis migrasi wajib dipilih (Pindah Kelas atau Kenaikan Kelas).',
            'migration_type.in' => 'Jenis migrasi tidak valid.',
            'student_ids.required' => 'Pilih setidaknya satu siswa untuk dipindahkan.',
            'student_ids.array' => 'Format data siswa tidak valid.',
            'student_ids.min' => 'Pilih setidaknya satu siswa untuk dipindahkan.',
            'new_classroom_id.required' => 'Kelas tujuan wajib dipilih.',
            'new_classroom_id.exists' => 'Kelas tujuan tidak ditemukan.',
            'academic_year_id.required' => 'Tahun ajaran target wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran target tidak ditemukan.',
        ];
    }
}
