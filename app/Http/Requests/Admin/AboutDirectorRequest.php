<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AboutDirectorRequest extends FormRequest
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
        return match ($this->method()) {
            'POST' => [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:255'], // 'director' or 'commissioner
                'position' => ['required', 'string', 'max:255'],
                'position_en' => ['required', 'string', 'max:255'],
                'quote' => ['required', 'string'],
                'quote_en' => ['required', 'string'],
                'image' => ['required', 'image'],
                'instagram_url' => ['nullable', 'string', 'max:255'],
                'linkedin_url' => ['nullable', 'string', 'max:255'],
                'facebook_url' => ['nullable', 'string', 'max:255'],
            ],
            'PUT', 'PATCH' => [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:255'], // 'director' or 'commissioner
                'position' => ['required', 'string', 'max:255'],
                'position_en' => ['required', 'string', 'max:255'],
                'quote' => ['required', 'string'],
                'quote_en' => ['required', 'string'],
                'image' => ['nullable', 'image'],
                'instagram_url' => ['nullable', 'string', 'max:255'],
                'linkedin_url' => ['nullable', 'string', 'max:255'],
                'facebook_url' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }
}
