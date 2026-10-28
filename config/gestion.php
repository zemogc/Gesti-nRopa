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

    /*
    |--------------------------------------------------------------------------
    | Limite de peticiones
    |--------------------------------------------------------------------------
    |
    | Numero maximo de peticiones por minuto a la API, por usuario autenticado
    | o, si no hay sesion, por direccion IP. Al superarlo se responde 429.
    |
    */

    'limite_por_minuto' => (int) env('API_LIMITE_POR_MINUTO', 120),

];
