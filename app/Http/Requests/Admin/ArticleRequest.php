<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
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
                'title' => ['required', 'string', 'max:255'],
                'title_en' => ['required', 'string', 'max:255'],
                'category_article_id' => ['required', 'exists:category_articles,id'],
                'content' => ['required', 'string'],
                'content_en' => ['required', 'string'],
                'image' => ['required', 'image', 'max:2048'],
                'type' => ['required', 'in:NEWS,ARTICLE'],
                'status' => ['required', 'in:PUBLISH,DRAFT']
            ],
            'PUT', 'PATCH' => [
                'title' => ['required', 'string', 'max:255'],
                'title_en' => ['required', 'string', 'max:255'],
                'category_article_id' => ['required', 'exists:category_articles,id'],
                'content' => ['required', 'string'],
                'content_en' => ['required', 'string'],
                'image' => ['nullable', 'image', 'max:2048'],
                'type' => ['required', 'in:NEWS,ARTICLE'],
                'status' => ['required', 'in:PUBLISH,DRAFT']
            ],
            default => [],
        };
    }
}
