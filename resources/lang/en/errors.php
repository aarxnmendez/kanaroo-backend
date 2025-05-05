<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for error messages.
    |
    */

    // HTTP Status Errors
    'unauthenticated' => 'Unauthenticated',
    'unauthorized' => 'Unauthorized',
    'resource_not_found' => 'Resource not found',
    'invalid_data' => 'The given data was invalid',
    'http_error' => 'HTTP error',
    'server_error' => 'Server error',

    // Form Validation
    'project_name_required' => 'The project name is required',
    'project_name_max' => 'The project name must not exceed :max characters',
    'project_name_unique' => 'You already have a project with this name',

    // Authorization
    'not_authorized_view' => 'Not authorized to view this resource',
    'not_authorized_update' => 'Not authorized to update this resource',
    'not_authorized_delete' => 'Not authorized to delete this resource',

    // Success messages
    'project_deleted' => 'Project deleted successfully',
];
