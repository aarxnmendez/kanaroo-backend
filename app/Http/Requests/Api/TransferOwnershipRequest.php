<?php

namespace App\Http\Requests\Api;

use App\Models\ProjectUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

/**
 * @property \App\Models\Project $project The project this request refers to (resolved from route model binding).
 * @property int $new_owner_id The ID of the user who will be the new owner.
 */
class TransferOwnershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Actual authorization is handled in ProjectPolicy@transferOwnership.
     */
    public function authorize(): bool
    {
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
            'new_owner_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id'),
                function ($attribute, $value, $fail) {
                    // Ensure the new owner is not the current owner.
                    if ($value == $this->project->user_id) {
                        $fail(__('api.transfer_ownership.cannot_transfer_to_self'));
                        return;
                    }

                    // Ensure the new owner is a member of the project.
                    $isMember = $this->project->users()->where('user_id', $value)->exists();
                    if (!$isMember) {
                        $fail(__('api.transfer_ownership.new_owner_not_member'));
                        return;
                    }
                },
            ],
        ];
    }

    /**
     * Get the custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'new_owner_id.required' => __('validation.transfer_ownership.new_owner_id_required'),
            'new_owner_id.integer' => __('validation.transfer_ownership.new_owner_id_integer'),
            'new_owner_id.exists' => __('validation.transfer_ownership.new_owner_id_exists'),
            // Messages for custom rules are defined in the language files.
        ];
    }
}
