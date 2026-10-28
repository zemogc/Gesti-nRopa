<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProveedoresController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['proveedores' => Proveedor::orderBy('nombre')->get()]);
    }

    public function store(ProveedorRequest $request): JsonResponse
    {
        $proveedor = Proveedor::create($request->validated());

        return response()->json(['proveedor' => $proveedor], 201);
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        return response()->json([
            'proveedor' => $proveedor,
            'productos' => $proveedor->productos()->orderBy('nombre')->get(['id', 'nombre', 'precio', 'stock']),
        ]);
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): JsonResponse
    {
        $proveedor->update($request->validated());

        return response()->json(['proveedor' => $proveedor]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        // SQLite no aplica la llave foranea de una columna agregada con ALTER TABLE,
        // por eso se desvinculan los productos explicitamente
        DB::transaction(function () use ($proveedor) {
            $proveedor->productos()->update(['proveedor_id' => null]);
            $proveedor->delete();
        });

        return response()->json([
            'success' => true,
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }
}
