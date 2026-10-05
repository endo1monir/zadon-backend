<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminOptions;
use App\Support\NotificationDispatcher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationRequest extends FormRequest
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
            'audience' => ['nullable', Rule::in(NotificationDispatcher::AUDIENCES)],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'message_ar' => ['nullable', 'string', 'max:2000'],
            'message_en' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(array_keys(AdminOptions::notificationTypes()))],
            'action_label_ar' => ['nullable', 'string', 'max:255'],
            'action_label_en' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title_ar.required' => __('admin.validation.notification_title_ar_required'),
            'type.in' => __('admin.validation.notification_type_in'),
        ];
    }
}
