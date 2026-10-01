<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
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
        $setting = $this->route('setting');

        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('settings', 'key')->ignore($setting),
            ],
            'value' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.required' => 'A key is required.',
            'key.alpha_dash' => 'The key may only contain letters, numbers, dashes and underscores.',
            'value.required' => 'A value is required.',
        ];
    }
}
