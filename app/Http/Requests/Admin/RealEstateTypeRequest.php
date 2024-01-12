<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RealEstateTypeRequest extends FormRequest
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
                'real_estate_id' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'bedroom' => ['required', 'numeric', 'max:255'],
                'bathroom' => ['required', 'numeric', 'max:255'],
                'garage' => ['required', 'numeric', 'max:255'],
                'price' => ['required', 'string', 'max:255'],
                'url' => ['required', 'string', 'max:255'],
                'image' => ['required', 'image'],
            ],
            'PUT', 'PATCH' => [
                // 'real_estate_id' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'bedroom' => ['required', 'numeric', 'max:255'],
                'bathroom' => ['required', 'numeric', 'max:255'],
                'garage' => ['required', 'numeric', 'max:255'],
                'price' => ['required', 'string', 'max:255'],
                'url' => ['required', 'string', 'max:255'],
                'image' => ['nullable', 'image'],
            ],
            default => [],
        };
    }
}
