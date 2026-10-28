<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Filtro opcional: ?buscar= (nombre, apellido o email)
     */
    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->query('buscar'), function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return response()->json(['clientes' => $clientes]);
    }

    public function store(ClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return response()->json(['cliente' => $cliente], 201);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json(['cliente' => $cliente]);
    }

    public function update(ClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($request->validated());

        return response()->json(['cliente' => $cliente]);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        // La llave foranea borra en cascada: se impide para no perder el historial de ventas
        if ($cliente->ventas()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el cliente porque tiene ventas registradas.',
            ], 409);
        }

        $cliente->delete();

        return response()->json([
            'success' => true,
            'clientes' => Cliente::orderBy('apellido')->orderBy('nombre')->get(),
        ]);
    }
}
