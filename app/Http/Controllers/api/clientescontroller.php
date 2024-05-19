<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::all();
        return json_encode(['clientes' => $clientes]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'max:30']
        ]);
        $cliente = new Cliente();
        $cliente->id = $request->id;
        $cliente->nombre = $request->nombre;
        $cliente->apellido = $request->apellido;
        $cliente->email = $request->email;
        $cliente->telefono = $request->telefono;
        $cliente->save();

        return json_encode(['cliente' => $cliente]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::find($id);
        if (is_null($cliente)) {
            return abort(404);
        }

        return json_encode(['cliente' => $cliente]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);

        $cliente->id = $request->id;
        $cliente->nombre = $request->nombre;
        $cliente->apellido = $request->apellido;
        $cliente->email = $request->email;
        $cliente->telefono = $request->telefono;
        $cliente->save();

        return json_encode(['cliente' => $cliente]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::find($id);
        $cliente->delete();

        $clientes = Cliente::all();
        return json_encode(['clientes' => $clientes, 'success' => true]);

    }
}
