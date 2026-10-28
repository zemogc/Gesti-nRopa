<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CategoriaController;
use App\Http\Controllers\api\ClientesController;
use App\Http\Controllers\api\ProductosController;
use App\Http\Controllers\api\ProveedoresController;
use App\Http\Controllers\api\ResumenController;
use App\Http\Controllers\api\VentasController;
use App\Http\Middleware\AutenticacionOpcional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Exigen token solo si API_REQUIERE_AUTH=true (ver config/gestion.php)
Route::middleware(AutenticacionOpcional::class)->group(function () {
    Route::get('/resumen', ResumenController::class)->name('resumen');

    Route::apiResource('categorias', CategoriaController::class)
        ->parameters(['categorias' => 'categoria']);

    Route::apiResource('clientes', ClientesController::class)
        ->parameters(['clientes' => 'cliente']);

    Route::apiResource('productos', ProductosController::class)
        ->parameters(['productos' => 'producto']);
    Route::post('/productos/{producto}/reabastecer', [ProductosController::class, 'reabastecer'])
        ->name('productos.reabastecer');

    Route::apiResource('proveedores', ProveedoresController::class)
        ->parameters(['proveedores' => 'proveedor']);

    Route::apiResource('ventas', VentasController::class)
        ->parameters(['ventas' => 'venta']);
});
