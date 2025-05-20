<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // The section_id is implicitly validated by being part of the route
        // and its existence is checked by Route Model Binding.
        // We don't need to validate its existence here again if it's from the route.

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['nullable', Rule::in(['todo', 'in_progress', 'done', 'blocked', 'archived'])],
            // 'section_id' is derived from the route parameter {section} and should not be in the request body for creation directly here.
            // It will be passed to the repository method from the controller.
            'assigned_to' => 'nullable|integer|exists:users,id', // Ensure user exists
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id' // Ensure all tag_ids are integers and exist in tags table
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => __('validation.item.title_required'),
            'due_date.date_format' => __('validation.item.due_date_format'),
            'priority.in' => __('validation.item.priority_in'),
            'status.in' => __('validation.item.status_in'),
            'assigned_to.exists' => __('validation.item.assigned_to_exists'),
            'tag_ids.array' => __('validation.item.tag_ids_array'),
            'tag_ids.*.integer' => __('validation.item.tag_id_integer'),
            'tag_ids.*.exists' => __('validation.item.tag_id_exists'),
        ];
    }
}
