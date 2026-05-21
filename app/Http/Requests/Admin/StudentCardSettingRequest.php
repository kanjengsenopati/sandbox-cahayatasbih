<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentCardSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_card_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Logo
            'layout.logo.show' => 'nullable',
            'layout.logo.top' => 'nullable|numeric|min:0|max:54',
            'layout.logo.left' => 'nullable|numeric|min:0|max:86',
            'layout.logo.width' => 'nullable|numeric|min:1|max:86',
            'layout.logo.height' => 'nullable|numeric|min:1|max:54',

            // Title
            'layout.title.show' => 'nullable',
            'layout.title.text' => 'nullable|string|max:100',
            'layout.title.color' => 'nullable|string|max:20',
            'layout.title.font_size' => 'nullable|integer|min:6|max:30',
            'layout.title.top' => 'nullable|numeric|min:0|max:54',
            'layout.title.left' => 'nullable|numeric|min:0|max:86',
            'layout.title.text_align' => 'nullable|in:left,center,right',
            'layout.title.font_weight' => 'nullable|in:normal,bold',

            // Subtitle
            'layout.subtitle.show' => 'nullable',
            'layout.subtitle.text' => 'nullable|string|max:100',
            'layout.subtitle.color' => 'nullable|string|max:20',
            'layout.subtitle.font_size' => 'nullable|integer|min:6|max:30',
            'layout.subtitle.top' => 'nullable|numeric|min:0|max:54',
            'layout.subtitle.left' => 'nullable|numeric|min:0|max:86',
            'layout.subtitle.text_align' => 'nullable|in:left,center,right',
            'layout.subtitle.font_weight' => 'nullable|in:normal,bold',

            // Photo
            'layout.photo.show' => 'nullable',
            'layout.photo.top' => 'nullable|numeric|min:0|max:54',
            'layout.photo.left' => 'nullable|numeric|min:0|max:86',
            'layout.photo.width' => 'nullable|numeric|min:5|max:40',
            'layout.photo.height' => 'nullable|numeric|min:5|max:50',
            'layout.photo.border_radius' => 'nullable|numeric|min:0|max:20',

            // Name
            'layout.name.show' => 'nullable',
            'layout.name.color' => 'nullable|string|max:20',
            'layout.name.font_size' => 'nullable|integer|min:6|max:30',
            'layout.name.top' => 'nullable|numeric|min:0|max:54',
            'layout.name.left' => 'nullable|numeric|min:0|max:86',
            'layout.name.font_weight' => 'nullable|in:normal,bold',

            // NIS
            'layout.nis.show' => 'nullable',
            'layout.nis.color' => 'nullable|string|max:20',
            'layout.nis.font_size' => 'nullable|integer|min:6|max:30',
            'layout.nis.top' => 'nullable|numeric|min:0|max:54',
            'layout.nis.left' => 'nullable|numeric|min:0|max:86',
            'layout.nis.font_weight' => 'nullable|in:normal,bold',
            'layout.nis.font_family' => 'nullable|string|max:50',

            // Classroom
            'layout.classroom.show' => 'nullable',
            'layout.classroom.color' => 'nullable|string|max:20',
            'layout.classroom.font_size' => 'nullable|integer|min:6|max:30',
            'layout.classroom.top' => 'nullable|numeric|min:0|max:54',
            'layout.classroom.left' => 'nullable|numeric|min:0|max:86',
            'layout.classroom.font_weight' => 'nullable|in:normal,bold',

            // School
            'layout.school.show' => 'nullable',
            'layout.school.color' => 'nullable|string|max:20',
            'layout.school.font_size' => 'nullable|integer|min:6|max:30',
            'layout.school.top' => 'nullable|numeric|min:0|max:54',
            'layout.school.left' => 'nullable|numeric|min:0|max:86',
            'layout.school.font_weight' => 'nullable|in:normal,bold',

            // Code (Barcode/QR)
            'layout.code.show' => 'nullable',
            'layout.code.type' => 'nullable|in:barcode,qrcode',
            'layout.code.top' => 'nullable|numeric|min:0|max:54',
            'layout.code.left' => 'nullable|numeric|min:0|max:86',
            'layout.code.width' => 'nullable|numeric|min:5|max:86',
            'layout.code.height' => 'nullable|numeric|min:3|max:30',
        ];
    }
}
