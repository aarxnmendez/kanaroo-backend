<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

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
        $rules = [
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
            // 'tag_ids.*' will be defined conditionally below
        ];

        $routeSection = $this->route('section');

        // Check if section model and project_id are available and valid
        if ($routeSection && property_exists($routeSection, 'project_id') && $routeSection->project_id) {
            $projectId = $routeSection->project_id;
            $rules['tag_ids.*'] = [
                'integer',
                Rule::exists('tags', 'id')->where(function ($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                })
            ];
        } else {
            // This block is reached if $routeSection or $routeSection->project_id is not available.
            // If 'tag_ids' are provided in the request, they cannot be properly validated against a project.
            // The 'tag_ids.*' rules apply to each item *if* 'tag_ids' is present and an array.
            $rules['tag_ids.*'] = [
                'integer', // Keep basic type check for individual items
                function ($attribute, $value, $fail) {
                    // This rule is applied to each tag ID if tag_ids is an array and not empty.
                    // Since we are in the 'else' (no project context), any tag ID is invalid.
                    $fail(__('validation.custom.tags_project_context_missing'));
                }
            ];
        }
        return $rules;
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
