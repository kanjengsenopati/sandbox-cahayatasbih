<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InformationRequest extends FormRequest
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
        return match ($this->method()) {
            'POST' => [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'information_category_id' => 'required|exists:information_categories,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ],
            'PUT', 'PATCH' => [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'information_category_id' => 'required|exists:information_categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ],
            default => [],
        };
    }
}
