<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Note: This only allows the request to be processed.
     * Specific authorization logic is handled by policies in the controller.
     */
    public function authorize(): bool
    {
        return true; // Allow the request to be processed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'description' => 'nullable|string',
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'archived', 'on_hold', 'completed']),
            ],
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
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
            'name.required' => __('errors.project_name_required'),
            'name.max' => __('errors.project_name_max'),
            'name.unique' => __('errors.project_name_unique'),
        ];
    }
}
