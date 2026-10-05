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
            'key.required' => __('admin.validation.setting_key_required'),
            'key.alpha_dash' => __('admin.validation.setting_key_alpha_dash'),
            'value.required' => __('admin.validation.setting_value_required'),
        ];
    }
}
