<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRateRequest extends FormRequest
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
        $rules = [
            'bill_type_id' => 'required|exists:bill_types,id',
            'price' => 'required|numeric',
            'type' => 'required|in:REGULAR,TRANSFER',
        ];

        // Validation for CREATE only
        if ($this->isMethod('post')) {
            $rules['classrooms'] = 'required_if:type,REGULAR|array';
            $rules['students'] = 'required_if:type,TRANSFER|array';
            
            // For monthly, we can allow price to be 0 or null technically if months are filled, 
            // but the form usually forces a value. keeping price required is safer.
        }

        return $rules;
    }
}
