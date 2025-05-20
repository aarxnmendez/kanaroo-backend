<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy in controller handles authorization.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date_format:Y-m-d',
            'priority' => ['sometimes', 'nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['sometimes', 'nullable', Rule::in(['todo', 'in_progress', 'done', 'blocked', 'archived'])],
            // section_id generally should not be updated directly for an item once created,
            // moving an item to another section might be a different operation or handled carefully.
            // If allowed, it would need 'sometimes|required|integer|exists:sections,id'
            'assigned_to' => 'sometimes|nullable|integer|exists:users,id',
            'tag_ids' => 'sometimes|nullable|array',
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
