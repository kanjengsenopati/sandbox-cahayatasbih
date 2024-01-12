<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CorporateSocialResponsibilityRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description' => 'required|string',
            'description_en' => 'required|string',
            'image' => 'nullable|image',
            'image_en' => 'nullable|image',
            'content_title' => 'required|string|max:255',
            'content_title_en' => 'required|string|max:255',
            'content_description' => 'required|string',
            'content_description_en' => 'required|string',
        ];
    }
}
