<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the same data the vendor product endpoints accept
 * (POST/PUT /api/vendor/products). The admin form collects the identical keys so
 * a product created here is indistinguishable from one created by the vendor.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('options')) {
            return;
        }

        $options = is_array($this->input('options'))
            ? $this->input('options')
            : json_decode((string) $this->input('options'), true);

        // Invalid JSON stays a string so the `array` rule reports it instead of
        // being silently swallowed.
        $this->merge(['options' => is_array($options) ? $options : $this->input('options')]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $required = $product === null ? 'required' : 'sometimes';

        return [
            'name_ar' => [$required, 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'price' => [$required, 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'min_stock_alert' => ['sometimes', 'integer', 'min:0'],
            'unit_ar' => [$required, 'string', 'max:50'],
            'unit_en' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],
            'storage_method' => ['nullable', 'string', 'max:255'],
            'storage_temp' => ['nullable', Rule::in(['ambient', 'chilled', 'frozen'])],
            'expiry_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'is_prescription_required' => ['sometimes', 'boolean'],
            'options' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_ar.required' => 'The product name in Arabic is required.',
            'price.required' => 'The price is required.',
            'unit_ar.required' => 'The Arabic unit is required.',
            'options.array' => 'Options must be valid JSON, for example [{"name":"1 kg","price_surplus":0}].',
        ];
    }

    /**
     * The product attributes, keyed exactly like the vendor product payload.
     *
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        $validated = $this->safe();

        $attributes = [
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'price' => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'unit_ar' => $validated['unit_ar'],
            'unit_en' => $validated['unit_en'] ?? null,
            'country_of_origin' => $validated['country_of_origin'] ?? null,
            'storage_method' => $validated['storage_method'] ?? null,
            'storage_temp' => $validated['storage_temp'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_prescription_required' => $this->has('is_prescription_required')
                ? $this->boolean('is_prescription_required')
                : false,
            'options' => $this->filled('options') ? $validated['options'] : null,
        ];

        // stock, min_stock_alert and cost_price are not nullable in the schema, so an
        // omitted field keeps the schema default on create and the stored value on update.
        foreach (['stock', 'min_stock_alert'] as $field) {
            if ($this->filled($field)) {
                $attributes[$field] = $this->integer($field);
            }
        }

        if ($this->filled('cost_price')) {
            $attributes['cost_price'] = $validated['cost_price'];
        }

        return $attributes;
    }
}
