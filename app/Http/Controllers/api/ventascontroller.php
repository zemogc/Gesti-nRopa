<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::all();
        return response()->json(['ventas' => $ventas], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer',
            'fecha_venta' => 'required|date',
            'total' => 'required|numeric',
        ]);

        $venta = Venta::create([
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'fecha_venta' => $request->fecha_venta,
            'total' => $request->total,
        ]);

        return response()->json(['venta' => $venta], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta not found'], 404);
        }

        return response()->json(['venta' => $venta], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer',
            'fecha_venta' => 'required|date',
            'total' => 'required|numeric',
        ]);

        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta not found'], 404);
        }

        $venta->update([
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'fecha_venta' => $request->fecha_venta,
            'total' => $request->total,
        ]);

        return response()->json(['venta' => $venta], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta not found'], 404);
        }

        $venta->delete();

        return response()->json(['message' => 'Venta deleted successfully'], 200);
    }
}
