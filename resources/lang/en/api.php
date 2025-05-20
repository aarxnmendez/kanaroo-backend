<?php

// lang/en/api.php
return [
    'project_member' => [
        'add_conflict'          => 'User is already a member of this project or could not be added.',
        'update_role_failed'    => 'Could not update member role. User may not be a member, or the role change is not permitted (e.g., attempting to change owner\'s role).',
        'remove_failed'         => 'Could not remove member. User may not be a member, is the project owner, or an admin attempting to self-remove.',
    ],
    // Add other API specific messages here if needed
    'project' => [
        'leave_success' => 'User successfully left the project.',
        'leave_failed'  => 'Failed to leave the project.',
    ],
];
