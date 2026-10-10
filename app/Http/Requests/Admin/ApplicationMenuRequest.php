<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type', 'internal');
        $flag = $this->input('flag');

        if (empty($flag) && in_array($type, ['whatsapp', 'external'])) {
            $prefix = $type === 'whatsapp' ? 'wa_' : 'ext_';
            $flag = $prefix . \Illuminate\Support\Str::slug($this->input('name', 'menu'), '_');
            $this->merge(['flag' => $flag]);
        }

        if (empty($this->input('type'))) {
            $this->merge(['type' => 'internal']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:internal,whatsapp,external'],
            'officer_id' => ['nullable', 'string', 'exists:officers,id'],
            'flag' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'url' => ['nullable', 'string'],
            'wa_number' => ['nullable', 'string', 'max:50'],
            'wa_message' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
