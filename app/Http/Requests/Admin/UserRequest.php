<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                'email' => 'nullable|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'gender' => 'required',
                'phone' => 'required',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'jamaah_status' => 'required|in:JAMAAH,NON_JAMAAH,UNKNOWN,MUKIMIN',
            ],
            'PUT' => [
                'name' => 'required',
                'email' => 'nullable|email|unique:users,email,' . $this->user->id,
                'password' => 'nullable|min:8|confirmed',
                'gender' => 'required',
                'phone' => 'required',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'jamaah_status' => 'required|in:JAMAAH,NON_JAMAAH,UNKNOWN,MUKIMIN',
            ],
            default => [],
        };
    }
}
