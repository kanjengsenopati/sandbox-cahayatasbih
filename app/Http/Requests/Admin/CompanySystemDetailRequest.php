<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanySystemDetailRequest extends FormRequest
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
                'title' => 'required',
                'title_en' => 'required',
                'description' => 'required',
                'description_en' => 'required',
                'image' => 'required|image',
            ],
            'PUT', 'PATCH' => [
                'title' => 'required',
                'title_en' => 'required',
                'description' => 'required',
                'description_en' => 'required',
                'image' => 'image',
            ],
            default => [],
        };
    }
}
