<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => [
                'category_warehouse_id' => ['required', 'exists:category_warehouses,id'],
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                // 'infrastructure_description' => ['required', 'string'],
                // 'infrastructure_description_en' => ['required', 'string'],

                'development_year' => ['required', 'integer'],
                'unit' => ['required', 'integer'],
                'strength' => ['required', 'string'],
                'roof' => ['required', 'string'],
                'electricity' => ['required', 'string'],
                'capacity' => ['required', 'string'],
                'image.*' => ['required', 'image'],
            ],
            'PUT', 'PATCH' => [
                'category_warehouse_id' => ['required', 'exists:category_warehouses,id'],
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                // 'infrastructure_description' => ['required', 'string'],
                // 'infrastructure_description_en' => ['required', 'string'],
                'development_year' => ['required', 'integer'],
                'unit' => ['required', 'integer'],
                'strength' => ['required', 'string'],
                'roof' => ['required', 'string'],
                'electricity' => ['required', 'string'],
                'capacity' => ['required', 'string'],
                'image.*' => ['nullable', 'image'],
            ],
            default => [],
        };
    }
}
