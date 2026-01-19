<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationSettingRequest extends FormRequest
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
        return [
            'payment_expire_time' => 'required',
            'link_whatsapp' => 'required',
            'number_whatsapp' => 'required',
            'device_id' => 'required',
            'payment_fee' => 'required|numeric',
            'student_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bill_fee' => 'required|numeric',
            'saldo_fee' => 'required|numeric',
            // 'target_month' => 'required|numeric',
            // 'target_year' => 'required|numeric',
        ];
    }
}
