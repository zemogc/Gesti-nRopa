<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = DB::table('ventas')
            ->join('productos', 'ventas.producto_id', '=', 'productos.id')
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->select('ventas.*', 'productos.nombre as producto_nombre', 'clientes.nombre as cliente_nombre', 'clientes.apellido as cliente_apellido')
            ->get();

        return json_encode(['ventas' => $ventas]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => ['required', 'max:30']
        ]);
        $venta = new Venta();
        $venta->id = $request->id;
        $venta->producto_id = $request->producto_id;
        $venta->cliente_id = $request->cliente_id;
        $venta->cantidad = $request->cantidad;
        $venta->fecha_venta = $request->fecha_venta;
        $venta->total = $request->total;
        $venta->save();

        return json_encode(['venta' => $venta]);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $venta = Venta::find($id);
        if (is_null($venta)) {
            return abort(404);
        }
        $productos = Producto::all();
        $clientes = Cliente::all();

        return json_encode(['venta' => $venta, 'productos' => $productos, 'clientes' => $clientes]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $venta = Venta::find($id);
        $venta->id = $request->id;
        $venta->producto_id = $request->producto_id;
        $venta->cliente_id = $request->cliente_id;
        $venta->cantidad = $request->cantidad;
        $venta->fecha_venta = $request->fecha_venta;
        $venta->total = $request->total;
        $venta->save();

        return json_encode(['venta' => $venta]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $venta = Venta::find($id);
        $venta->delete();

        $ventas = DB::table('ventas')
            ->join('productos', 'ventas.producto_id', '=', 'productos.id')
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->select('ventas.*', 'productos.nombre as producto_nombre', 'clientes.nombre as cliente_nombre', 'clientes.apellido as cliente_apellido')
            ->get();

        return json_encode(['ventas' => $ventas, 'success' => true]);
    }
}
