<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RealEstateRequest extends FormRequest
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
                'url' => ['nullable', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'image' => ['required', 'image'],
            ],
            'PUT', 'PATCH' => [
                'name' => ['required', 'string', 'max:255'],
                'url' => ['nullable', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'image' => ['nullable', 'image'],
            ],
            default => [],
        };
    }
}
