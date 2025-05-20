<?php

// lang/es/api.php
return [
    'project_member' => [
        'add_conflict'          => 'El usuario ya es miembro de este proyecto o no pudo ser añadido.', // Example translation
        'update_role_failed'    => 'No se pudo actualizar el rol del miembro. El usuario puede no ser miembro o el cambio de rol no está permitido.', // Example translation
        'remove_failed'         => 'No se pudo eliminar al miembro. El usuario puede no ser miembro, es el propietario del proyecto, o un administrador intentando auto-eliminarse.', // Example translation
    ],
    'transfer_ownership' => [
        'cannot_transfer_to_self' => 'No puedes transferirte la propiedad a ti mismo.',
        'new_owner_not_member' => 'El usuario seleccionado no es miembro de este proyecto y no puede convertirse en su propietario.',
        'new_owner_id_required' => 'El ID del nuevo propietario es obligatorio.',
        'new_owner_id_integer' => 'El ID del nuevo propietario debe ser un número entero.',
        'new_owner_id_exists' => 'El nuevo propietario seleccionado no existe.',
        'success' => 'La propiedad del proyecto se ha transferido correctamente.',
        'failed' => 'Error al transferir la propiedad del proyecto.'
    ],
    // Add other API specific messages here and translate them to Spanish
    'project' => [
        'leave_success' => 'El usuario ha abandonado el proyecto correctamente.',
        'leave_failed'  => 'Error al intentar abandonar el proyecto.',
    ],
];
