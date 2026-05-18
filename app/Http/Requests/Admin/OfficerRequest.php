<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OfficerRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'duty' => 'required|string',
            'phone' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'access_scope' => 'required|in:pwa,backoffice,both',
        ];
    }
}
