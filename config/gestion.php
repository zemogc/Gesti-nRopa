<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Autenticacion de la API
    |--------------------------------------------------------------------------
    |
    | Si es true, todos los endpoints (excepto el login) exigen un token de
    | Sanctum en la cabecera "Authorization: Bearer <token>". Por defecto es
    | false para no romper a los clientes que ya consumen la API sin token.
    |
    */

    'requiere_autenticacion' => (bool) env('API_REQUIERE_AUTH', false),

];
