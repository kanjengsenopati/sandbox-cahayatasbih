<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class WaliPpdbRequest extends FormRequest
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
            'ppdb_id' => 'required|exists:ppdbs,id',
            'name' => 'required|string',
            'place_of_birth' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:L,P',
            'address' => 'required|string',
            'nisn' => 'required|string',
            'nik' => 'required|string',
            'origin_school' => 'required|string',
            'family_card_number' => 'required|string',
            'father_name' => 'required|string',
            'father_nik' => 'required|string',
            'father_status' => 'required|string',
            'father_education' => 'required|string',
            'father_job' => 'required|string',
            'mother_name' => 'required|string',
            'mother_nik' => 'required|string',
            'mother_status' => 'required|string',
            'mother_education' => 'required|string',
            'mother_job' => 'required|string',
            'government_aid_card_type' => 'required|string',
            'mdti_branch' => 'nullable|string',
            'member_number' => 'nullable|string',
            'photo_card' => 'nullable|image|mimes:jpeg,png,jpg',
        ];
    }
}
