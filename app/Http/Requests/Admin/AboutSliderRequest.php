<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AboutSliderRequest extends FormRequest
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
                'name' => 'required',
                'file' => 'required|file|mimes:jpg,jpeg,png,mp4,webm,ogg,3gp',
                'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png'
            ],
            'PUT', 'PATCH' => [
                'name' => 'required',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,mp4,webm,ogg,3gp',
                'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png'
            ],
            default => [],
        };
    }
}
