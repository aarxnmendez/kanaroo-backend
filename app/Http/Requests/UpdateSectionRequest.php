<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateSectionRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'filter_type' => ['sometimes', 'string', Rule::in(['none', 'status', 'tag', 'date', 'priority', 'assigned_to'])],
            'filter_value' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'item_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Only run these additional checks if filter_type or filter_value is present in the request
            if (!$this->has('filter_type') && !$this->has('filter_value')) {
                return;
            }

            $filterType = $this->input('filter_type', $this->section?->filter_type); // Default to existing if not provided
            $filterValue = $this->input('filter_value');

            // If filter_value is explicitly set to null, no further specific validation needed on its content
            if (is_null($filterValue) && $this->has('filter_value')) return;
            // If filter_value is not provided in request but filter_type is, it implies clearing or no value for type.
            if (!$this->has('filter_value') && $this->has('filter_type')) return;
            if (empty($filterValue) && !$this->has('filter_value')) return; // Not provided, do nothing
            if (empty($filterValue) && $this->has('filter_value')) return; // Provided as empty, nullable allows this

            switch ($filterType) {
                case 'date':
                    if (!json_validate($filterValue)) {
                        $validator->errors()->add('filter_value', __('validation.section.filter_value_json'));
                    }
                    break;
                case 'tag':
                    if (!ctype_digit(strval($filterValue)) || !\App\Models\Tag::where('id', $filterValue)->exists()) {
                        $validator->errors()->add('filter_value', __('validation.section.filter_value_tag_exists'));
                    }
                    break;
                case 'assigned_to':
                    if (!ctype_digit(strval($filterValue)) || !\App\Models\User::where('id', $filterValue)->exists()) {
                        $validator->errors()->add('filter_value', __('validation.section.filter_value_user_exists'));
                    }
                    break;
            }
        });
    }
}
