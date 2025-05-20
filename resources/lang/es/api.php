<?php

// lang/es/api.php
return [
    'project_member' => [
        'add_conflict'          => 'El usuario ya es miembro de este proyecto o no pudo ser añadido.', // Example translation
        'update_role_failed'    => 'No se pudo actualizar el rol del miembro. El usuario puede no ser miembro o el cambio de rol no está permitido.', // Example translation
        'remove_failed'         => 'No se pudo eliminar al miembro. El usuario puede no ser miembro, es el propietario del proyecto, o un administrador intentando auto-eliminarse.', // Example translation
    ],
    // Add other API specific messages here and translate them to Spanish
    'project' => [
        'leave_success' => 'El usuario ha abandonado el proyecto correctamente.',
        'leave_failed'  => 'Error al intentar abandonar el proyecto.',
    ],
];
