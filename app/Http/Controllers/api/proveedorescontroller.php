<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Proveedores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proveedores = Proveedores::all();
        return json_encode(['proveedores' => $proveedores]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'max:30']
        ]);
        $proveedor = new Proveedores();
        $proveedor->id = $request->id;
        $proveedor->nombre = $request->nombre;
        $proveedor->contacto = $request->contacto;
        $proveedor->save();

        return json_encode(['proveedor' => $proveedor]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $proveedor = Proveedores::find($id);
        if (is_null($proveedor)) {
            return abort(404);
        }

        return json_encode(['proveedor' => $proveedor]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $proveedor = Proveedores::find($id);

        $proveedor->id = $request->id;
        $proveedor->nombre = $request->nombre;
        $proveedor->contacto = $request->contacto;
        $proveedor->save();

        return json_encode(['proveedor' => $proveedor]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $proveedor = Proveedores::find($id);
        $proveedor ->delete();

        $proveedores = Proveedores::all();
        return json_encode(['proveedores' => $proveedores, 'success' => true]);

    }
}
