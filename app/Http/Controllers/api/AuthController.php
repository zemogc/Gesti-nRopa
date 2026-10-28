<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Emite un token de acceso a partir de email y contrasena.
     */
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = User::where('email', $datos['email'])->first();

        if (! $usuario || ! Hash::check($datos['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $token = $usuario->createToken($datos['dispositivo'] ?? 'api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'tipo' => 'Bearer',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Revoca el token usado en la peticion.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }
}
