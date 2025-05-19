<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class StoreSectionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'filter_type' => ['sometimes', 'string', Rule::in(['none', 'status', 'tag', 'date', 'priority', 'assigned_to'])],
            'filter_value' => ['nullable', 'string', 'max:1000'],
            'item_limit' => ['nullable', 'integer', 'min:1'],
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
            $filterType = $this->input('filter_type');
            $filterValue = $this->input('filter_value');

            if (empty($filterValue)) return; // No further validation if value is empty

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
                    // For 'status' and 'priority', string validation in rules() is usually sufficient.
            }
        });
    }
}
