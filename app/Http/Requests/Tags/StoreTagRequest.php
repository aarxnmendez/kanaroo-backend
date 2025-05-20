<?php

namespace App\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy authorization in controller TagPolicy@create with Project context
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('project')->id; // Project from route model binding

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tags', 'name')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                }),
            ],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[a-fA-F0-9]{6}$/'], // Basic hex color validation
            // project_id is implicit from the route, not part of request body
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.tag.name_required'),
            'name.unique' => __('validation.tag.name_unique_project'),
            'color.regex' => __('validation.tag.color_regex'),
        ];
    }
}
