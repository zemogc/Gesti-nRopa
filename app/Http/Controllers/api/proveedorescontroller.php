<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proveedores;

class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proveedores = Proveedores::all();
        return response()->json(['proveedores' => $proveedores], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $proveedor = new Proveedores();
        $proveedor->nombre = $request->nombre;
        $proveedor->contacto = $request->contacto;
        $proveedor->save();

        return response()->json(['message' => 'Proveedor creado correctamente', 'proveedor' => $proveedor], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $proveedor = Proveedores::find($id);

        if ($proveedor) {
            return response()->json(['proveedor' => $proveedor], 200);
        } else {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $proveedor = Proveedores::find($id);

        if ($proveedor) {
            $proveedor->nombre = $request->nombre;
            $proveedor->contacto = $request->contacto;
            $proveedor->save();

            return response()->json(['message' => 'Proveedor actualizado correctamente', 'proveedor' => $proveedor], 200);
        } else {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $proveedor = Proveedores::find($id);

        if ($proveedor) {
            $proveedor->delete();
            return response()->json(['message' => 'Proveedor eliminado correctamente'], 200);
        } else {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }
}
