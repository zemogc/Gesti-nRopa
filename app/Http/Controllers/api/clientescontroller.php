<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json(['clientes' => $clientes], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $cliente = new Cliente();
        $cliente->nombre = $request->nombre;
        $cliente->apellido = $request->apellido;
        $cliente->email = $request->email;
        $cliente->telefono = $request->telefono;
        $cliente->save();

        return response()->json(['message' => 'Cliente creado correctamente', 'cliente' => $cliente], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            return response()->json(['cliente' => $cliente], 200);
        } else {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            $cliente->nombre = $request->nombre;
            $cliente->apellido = $request->apellido;
            $cliente->email = $request->email;
            $cliente->telefono = $request->telefono;
            $cliente->save();

            return response()->json(['message' => 'Cliente actualizado correctamente', 'cliente' => $cliente], 200);
        } else {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            $cliente->delete();
            return response()->json(['message' => 'Cliente eliminado correctamente'], 200);
        } else {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }
}
