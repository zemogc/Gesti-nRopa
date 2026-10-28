<?php

/*
| Mensajes de validacion en espanol para las reglas que usa la API.
| Las reglas que no aparecen aqui usan el mensaje en ingles de Laravel.
*/

return [
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmacion de :attribute no coincide.',
    'date' => 'El campo :attribute debe ser una fecha valida.',
    'email' => 'El campo :attribute debe ser un correo electronico valido.',
    'exists' => 'El :attribute seleccionado no existe.',
    'integer' => 'El campo :attribute debe ser un numero entero.',
    'max' => [
        'array' => 'El campo :attribute no debe tener mas de :max elementos.',
        'file' => 'El archivo :attribute no debe pesar mas de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener mas de :max caracteres.',
    ],
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un numero.',
    'regex' => 'El formato del campo :attribute no es valido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El :attribute ya esta registrado.',

    'attributes' => [
        'nombre' => 'nombre',
        'apellido' => 'apellido',
        'descripcion' => 'descripcion',
        'email' => 'correo electronico',
        'telefono' => 'telefono',
        'contacto' => 'contacto',
        'precio' => 'precio',
        'stock' => 'stock',
        'cantidad' => 'cantidad',
        'categoria_id' => 'categoria',
        'cliente_id' => 'cliente',
        'producto_id' => 'producto',
        'proveedor_id' => 'proveedor',
        'fecha_venta' => 'fecha de venta',
        'desde' => 'desde',
        'hasta' => 'hasta',
        'password' => 'contrasena',
    ],
];
