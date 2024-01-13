<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndustrialEstateRequest extends FormRequest
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
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'infrastructure_description' => ['required', 'string'],
                'infrastructure_description_en' => ['required', 'string'],
                'development_year' => ['required', 'integer'],
                'market_segment' => ['required', 'string'],
                'market_segment_en' => ['required', 'string'],
                'additional_area_for_sale' => ['required'],
                'total_area' => ['required'],
                'available_stock' => ['required'],
                'industry_built' => ['required'],
                'image' => ['required', 'image'],
            ],
            'PUT', 'PATCH' => [
                'title' => ['required', 'string'],
                'title_en' => ['required', 'string'],
                'description' => ['required', 'string'],
                'description_en' => ['required', 'string'],
                'infrastructure_description' => ['required', 'string'],
                'infrastructure_description_en' => ['required', 'string'],
                'development_year' => ['required', 'integer'],
                'market_segment' => ['required', 'string'],
                'market_segment_en' => ['required', 'string'],
                'additional_area_for_sale' => ['required'],
                'total_area' => ['required'],
                'available_stock' => ['required'],
                'industry_built' => ['required'],
                'image' => ['nullable', 'image'],
            ],
            default => [],
        };
    }
}
