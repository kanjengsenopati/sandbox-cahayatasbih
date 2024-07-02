<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudyGradeRequest extends FormRequest
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
            'study_id' => ['required', 'uuid'],
            'student_id' => ['required', 'uuid'],
            'academic_year_id' => ['required', 'uuid'],
            'semester_id' => ['required', 'uuid'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'letter_grade' => ['required', 'string', 'max:255'],
        ];
    }
}
