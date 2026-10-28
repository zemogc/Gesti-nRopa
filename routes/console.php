<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('usuario:crear {nombre} {email} {--password= : Si se omite, se pide de forma interactiva}', function (string $nombre, string $email) {
    $password = $this->option('password') ?: $this->secret('Contrasena');

    $validador = Validator::make(
        ['email' => $email, 'password' => $password],
        ['email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:8']],
    );

    if ($validador->fails()) {
        foreach ($validador->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    User::create(['name' => $nombre, 'email' => $email, 'password' => $password]);
    $this->info("Usuario {$email} creado.");

    return 0;
})->purpose('Crear un usuario que pueda iniciar sesion en la API');
