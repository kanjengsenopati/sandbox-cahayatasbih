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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge([
                'price' => (int) preg_replace('/[^0-9]/', '', (string) $this->price),
            ]);
        }

        // Clean monthly inputs if present
        for ($i = 1; $i <= 12; $i++) {
            if ($this->has("bulan_$i")) {
                $this->merge([
                    "bulan_$i" => (int) preg_replace('/[^0-9]/', '', (string) $this->{"bulan_$i"}),
                ]);
            }
        }
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
            'type' => 'required|in:REGULAR,TRANSFER',
            'gender' => 'nullable|array',
            'gender.*' => 'in:L,P',
            'jamaah_status' => 'nullable|array',
            'jamaah_status.*' => 'in:JAMAAH,NON_JAMAAH,MUKIMIN',
            'alumni_status' => 'nullable|array',
            'alumni_status.*' => 'in:ALUMNI_SMP_MA,NON_ALUMNI',
            'student_sub_status_id' => 'nullable|uuid|exists:student_sub_statuses,id',
        ];

        if ($this->type == 'TRANSFER') {
            if ($this->isMethod('post')) {
                $rules['transfer_names'] = 'required|array';
                $rules['transfer_prices'] = 'required|array';
                $rules['transfer_students'] = 'required|array';
            } else {
                $rules['transfer_edit_name'] = 'required|string|max:255';
                $rules['price'] = 'required|numeric';
                $rules['students'] = 'required|array|min:1';
                $rules['students.*'] = 'required|uuid|exists:students,id';
            }
        } elseif ($this->has('is_matrix') && $this->is_matrix == 1) {
            $rules['matrix_price'] = 'required|array';
        } elseif ($this->has('is_matrix_pptq') && $this->is_matrix_pptq == 1) {
            $rules['matrix_price_pptq'] = 'required|array';
        } else {
            $rules['price'] = 'required|numeric';
        }

        // Validation for CREATE only
        if ($this->isMethod('post')) {
            $rules['classrooms'] = 'required_if:type,REGULAR|array';
        }

        return $rules;
    }
}
