<?php

// lang/en/api.php
return [
    'project_member' => [
        'add_conflict'          => 'User is already a member of this project or could not be added.',
        'update_role_failed'    => 'Could not update member role. User may not be a member, or the role change is not permitted (e.g., attempting to change owner\'s role).',
        'remove_failed'         => 'Could not remove member. User may not be a member, is the project owner, or an admin attempting to self-remove.',
    ],
    'transfer_ownership' => [
        'cannot_transfer_to_self' => 'You cannot transfer ownership to yourself.',
        'new_owner_not_member' => 'The selected user is not a member of this project and cannot become its owner.',
        'new_owner_id_required' => 'The new owner ID is required.',
        'new_owner_id_integer' => 'The new owner ID must be an integer.',
        'new_owner_id_exists' => 'The selected new owner does not exist.',
        'success' => 'Project ownership transferred successfully.',
        'failed' => 'Failed to transfer project ownership.'
    ],
    // Add other API specific messages here if needed
    'project' => [
        'not_found'     => 'Project not found.',
        'leave_success' => 'User successfully left the project.',
        'leave_failed'  => 'Failed to leave the project.',
    ],
];
