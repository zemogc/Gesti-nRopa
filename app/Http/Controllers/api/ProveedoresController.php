<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;

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
        return response()->json(['proveedor' => $proveedor]);
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): JsonResponse
    {
        $proveedor->update($request->validated());

        return response()->json(['proveedor' => $proveedor]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->delete();

        return response()->json([
            'success' => true,
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }
}
