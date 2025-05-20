<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property \App\Models\Section $section The section instance resolved from route model binding.
 */
class ReorderItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy in controller (SectionPolicy@update) handles authorization.
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
            'item_ids' => 'required|array',
            'item_ids.*' => [
                'required',
                'integer',
                Rule::exists('items', 'id')->where(function ($query) {
                    // Ensure the item belongs to the section being reordered.
                    // $this->route('section') resolves to the Section model instance from the route parameter.
                    $query->where('section_id', $this->section->id);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'item_ids.required' => __('validation.reorder.item_ids_required'),
            'item_ids.array' => __('validation.reorder.item_ids_array'),
            'item_ids.*.required' => __('validation.reorder.item_id_required'),
            'item_ids.*.integer' => __('validation.reorder.item_id_integer'),
            'item_ids.*.exists' => __('validation.reorder.item_id_exists'),
        ];
    }
}
