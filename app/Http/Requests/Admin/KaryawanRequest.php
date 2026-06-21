<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KaryawanRequest extends FormRequest
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
        $fields = ['gaji_bulan', 'gaji_hari', 'potongan_terlambat', 'potongan_absen'];
        $merge = [];
        foreach ($fields as $field) {
            if ($this->has($field) && $this->input($field) !== null) {
                $merge[$field] = str_replace('.', '', $this->input($field));
            }
        }
        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('karyawan') ? $this->route('karyawan')->id : null;

        return [
            'admin_id' => 'required|uuid|unique:karyawans,admin_id,' . $id,
            'kamar' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'outlet_id' => 'required|uuid|exists:outlets,id',
            'section' => 'nullable|string|max:255',
            'gaji_bulan' => 'required|numeric|min:0',
            'gaji_hari' => 'required|numeric|min:0',
            'hari_kerja' => 'required|integer|min:0',
            'potongan_terlambat' => 'nullable|numeric|min:0',
            'potongan_absen' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'admin_id.required' => 'Pengguna wajib dipilih.',
            'admin_id.unique' => 'Pengguna ini sudah terdaftar sebagai Karyawan.',
            'outlet_id.required' => 'Outlet wajib dipilih.',
            'gaji_bulan.required' => 'Gaji bulanan wajib diisi.',
            'gaji_hari.required' => 'Gaji per hari wajib diisi.',
            'hari_kerja.required' => 'Hari kerja wajib diisi.',
        ];
    }
}
