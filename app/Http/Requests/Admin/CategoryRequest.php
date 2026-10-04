<?php

namespace App\Http\Requests\Admin;

use App\Support\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $category = $this->route('category');

        return [
            'type' => ['required', Rule::in(['store', 'product'])],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category)],
            'icon' => ['nullable', 'image', 'mimes:svg,jpg,jpeg,png,webp', 'max:2048'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('type', $this->input('type')),
                Rule::notIn([$category?->id]),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Choose whether this category is for stores or products.',
            'name_ar.required' => 'The Arabic name is required.',
            'parent_id.exists' => 'The selected parent category is not available for this type.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name_ar' => $this->filled('name_ar') ? trim((string) $this->input('name_ar')) : null,
            'name_en' => $this->filled('name_en') ? trim((string) $this->input('name_en')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryAttributes(): array
    {
        $attributes = $this->safe()->except(['icon', 'slug']);
        $category = $this->route('category');

        $attributes['slug'] = match (true) {
            $this->filled('slug') => Slug::unique(
                (string) $this->input('slug'),
                'categories',
                $category?->id,
                $this->input('type', 'item'),
            ),
            $category !== null => $category->slug,
            default => Slug::unique(
                (string) ($this->input('name_en') ?: $this->input('name_ar')),
                'categories',
                null,
                $this->input('type', 'item'),
            ),
        };

        return $attributes;
    }
}
