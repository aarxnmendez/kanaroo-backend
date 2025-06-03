<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSectionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by the Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => [
                'required',
                'integer',
                Rule::exists('sections', 'id')->where(function ($query) {
                    // Ensure the section belongs to the project being reordered.
                    $query->where('project_id', $this->route('project')->id);
                }),
            ],
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
            'ordered_ids.required' => __('validation.reorder.section_ids_required'),
            'ordered_ids.array'    => __('validation.reorder.section_ids_array'),
            'ordered_ids.*.required' => __('validation.reorder.section_id_required'),
            'ordered_ids.*.integer'  => __('validation.reorder.section_id_integer'),
            'ordered_ids.*.exists'   => __('validation.reorder.section_id_exists'),
        ];
    }
}
