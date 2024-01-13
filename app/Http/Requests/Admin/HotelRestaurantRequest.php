<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HotelRestaurantRequest extends FormRequest
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
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'image' => ['required', 'image'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'url' => ['required', 'string'],
            ],
            'PUT', 'PATCH' => [
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'image' => ['nullable', 'image'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'url' => ['required', 'string'],
            ],
            default => [],
        };
    }
}
