<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => [
                'name' => 'required',
                'email' => 'required|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'school_id' => 'nullable|exists:schools,id',
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],
            'PUT' => [
                'name' => 'required',
                'email' => 'required|unique:users,email,' . $this->id,
                'password' => 'nullable|min:8|confirmed',
                'school_id' => 'nullable|exists:schools,id',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],
            default => [],
        };
    }
}
