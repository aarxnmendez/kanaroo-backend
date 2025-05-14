<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'filter_type' => ['sometimes', 'string', 'in:none,status,tag,date,priority,assigned_to'],
            'filter_value' => ['nullable', 'string', 'max:255'],
            'item_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
