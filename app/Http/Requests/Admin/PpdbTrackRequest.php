<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PpdbTrackRequest extends FormRequest
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
            'ppdb_wave_id' => 'required|exists:ppdb_waves,id',
            'school_id' => 'required|exists:schools,id',
            'registration_type' => 'required|string',
            'registration_fee' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'is_open' => 'required|boolean',
            'close_reason' => 'nullable|string|max:500',
            'link_whatsapp_group' => 'nullable|string|url|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ppdb_wave_id.required' => 'Gelombang PPDB harus dipilih',
            'ppdb_wave_id.exists' => 'Gelombang PPDB tidak ditemukan',
            'school_id.required' => 'Sekolah harus dipilih',
            'school_id.exists' => 'Sekolah tidak ditemukan',
            'registration_type.required' => 'Tipe pendaftaran harus diisi',
            'registration_fee.required' => 'Biaya pendaftaran harus diisi',
            'registration_fee.numeric' => 'Biaya pendaftaran harus berupa angka',
            'registration_fee.min' => 'Biaya pendaftaran tidak boleh negatif',
            'quota.required' => 'Kuota harus diisi',
            'quota.integer' => 'Kuota harus berupa angka bulat',
            'quota.min' => 'Kuota minimal 1',
            'is_open.required' => 'Status harus dipilih',
            'is_open.boolean' => 'Status harus berupa boolean',
            'close_reason.max' => 'Alasan penutupan maksimal 500 karakter',
        ];
    }
}
