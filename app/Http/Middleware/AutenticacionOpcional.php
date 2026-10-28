<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige un token de Sanctum solo cuando la configuracion gestion.requiere_autenticacion esta activa.
 */
class AutenticacionOpcional
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('gestion.requiere_autenticacion') && ! Auth::guard('sanctum')->check()) {
            throw new AuthenticationException('No autenticado.', ['sanctum']);
        }

        return $next($request);
    }
}
