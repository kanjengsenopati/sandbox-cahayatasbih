<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentAchievementRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'school_id' => 'required|exists:schools,id',
            'semester' => 'required|in:GENAP,GANJIL',
            'title' => 'required|string',
            'champion' => 'required|string',
            'level' => 'required|string',
            'reward' => 'required|string',
        ];
    }
}
