<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CashFlowRequest extends FormRequest
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
            'receiver_id' => ['required', 'uuid', 'exists:admins,id'],
            'cash_flow_category_id' => ['required', 'uuid', 'exists:cash_flow_categories,id'],
            'type' => ['required', 'string', 'max:255'],
            'amount' => ['required'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'proof_of_payment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }
}
