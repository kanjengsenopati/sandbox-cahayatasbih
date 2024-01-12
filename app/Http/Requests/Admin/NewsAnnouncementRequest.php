<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NewsAnnouncementRequest extends FormRequest
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
                'title' => 'required|max:255',
                'title_en' => 'required|max:255',
                'description' => 'required',
                'description_en' => 'required',
                // 'file_name' => 'required',
                'file' => 'required',
                'created_by' => 'required',
            ],
            'PUT' => [
                'title' => 'required|max:255',
                'title_en' => 'required|max:255',
                'description' => 'required',
                'description_en' => 'required',
                // 'file_name' => 'required',
                'file' => 'nullable',
                'created_by' => 'required',
            ],
            default => [],
        };
    }
}
