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
        $adminId = $this->admin ? ($this->admin instanceof \App\Models\Admin ? $this->admin->id : $this->admin) : $this->route('admin');

        $avatarRules = ['max:2048'];
        if (extension_loaded('fileinfo')) {
            $avatarRules[] = 'image';
            $avatarRules[] = 'mimes:jpeg,png,jpg,gif,svg';
        } else {
            $avatarRules[] = function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
                        $fail('Kolom ' . $attribute . ' harus berupa file gambar (jpeg, png, jpg, gif, svg).');
                    }
                }
            };
        }

        $postAvatarRules = array_merge(['required'], $avatarRules);
        $putAvatarRules = array_merge(['nullable'], $avatarRules);

        return match ($this->method()) {
            'POST' => [
                'name' => 'required',
                'email' => 'required|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'school_id' => 'nullable|exists:schools,id',
                'avatar' => $postAvatarRules,
                'phone' => 'nullable|string|max:20|unique:admins,phone',
                'access_scope' => 'required|in:backoffice,pwa,both',
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ],
            'PUT' => [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'nullable|min:8|confirmed',
                'school_id' => 'nullable|exists:schools,id',
                'avatar' => $putAvatarRules,
                'phone' => 'nullable|string|max:20|unique:admins,phone,' . $adminId,
                'access_scope' => 'required|in:backoffice,pwa,both',
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ],
            default => [],
        };
    }
}
