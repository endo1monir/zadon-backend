<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($admin),
            ],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('admin.validation.admin_name_required'),
            'email.required' => __('admin.validation.admin_email_required'),
            'password.required' => __('admin.validation.admin_password_required'),
        ];
    }

    /**
     * The attributes an admin account is created or updated with.
     *
     * @return array<string, mixed>
     */
    public function adminAttributes(): array
    {
        $attributes = array_merge($this->safe()->except(['avatar', 'password']), [
            'role' => 'admin',
        ]);

        if ($this->filled('password')) {
            $attributes['password'] = $this->string('password')->toString();
        }

        return $attributes;
    }
}
