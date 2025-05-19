<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Policies\ProjectPolicy; // For role constants

class AddProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy authorization done in controller ProjectPolicy@addMember
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
            'user_id' => 'required|integer|exists:users,id',
            'role' => [
                'required',
                'string',
                Rule::in([ProjectPolicy::ROLE_ADMIN, ProjectPolicy::ROLE_EDITOR, ProjectPolicy::ROLE_MEMBER]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('validation.project_member.user_id_required'),
            'user_id.integer' => __('validation.project_member.user_id_integer'),
            'user_id.exists' => __('validation.project_member.user_id_exists'),
            'role.required' => __('validation.project_member.role_required'),
            'role.string' => __('validation.project_member.role_string'),
            'role.in' => __('validation.project_member.role_in'),
        ];
    }
}
