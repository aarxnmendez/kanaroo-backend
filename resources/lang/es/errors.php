<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Líneas de idioma para errores
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas de idioma se utilizan para mensajes de error.
    |
    */

    // Errores de estado HTTP
    'unauthenticated' => 'No autenticado',
    'unauthorized' => 'No autorizado',
    'resource_not_found' => 'Recurso no encontrado',
    'invalid_data' => 'Los datos proporcionados no son válidos',
    'http_error' => 'Error HTTP',
    'server_error' => 'Error del servidor',

    // Validación de formularios
    'project_name_required' => 'El nombre del proyecto es obligatorio',
    'project_name_max' => 'El nombre del proyecto no debe exceder los :max caracteres',
    'project_name_unique' => 'Ya tienes un proyecto con este nombre',

    // Autorización
    'not_authorized_view' => 'No está autorizado para ver este recurso',
    'not_authorized_update' => 'No está autorizado para actualizar este recurso',
    'not_authorized_delete' => 'No está autorizado para eliminar este recurso',
    'not_authorized_create' => 'No está autorizado para crear este recurso',

    // Mensajes de éxito
    'project_deleted' => 'Proyecto eliminado correctamente',
];
