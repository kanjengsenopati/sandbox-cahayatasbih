<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AboutVisionMissionRequest extends FormRequest
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
        return [
            'vision' => 'required|string',
            'vision_en' => 'required|string',
            'vision_description' => 'required|string',
            'vision_description_en' => 'required|string',
            'mission' => 'required|string',
            'mission_en' => 'required|string',
            'mission_description' => 'required|string',
            'mission_description_en' => 'required|string',
        ];
    }
}
