<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'link' => ['required', 'url', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:svg,jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_ar.required' => 'The Arabic name is required.',
            'link.required' => 'The profile link is required.',
            'link.url' => 'The link must be a valid URL, including https://',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name_ar' => $this->filled('name_ar') ? trim((string) $this->input('name_ar')) : null,
            'name_en' => $this->filled('name_en') ? trim((string) $this->input('name_en')) : null,
            'link' => $this->filled('link') ? trim((string) $this->input('link')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function socialAttributes(): array
    {
        return $this->safe()->except('icon');
    }
}
