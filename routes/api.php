<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ProductosController;
use App\Http\Controllers\api\CategoriaController;
use App\Http\Controllers\api\ClientesController;
use App\Http\Controllers\api\ProveedoresController;
use App\Http\Controllers\api\VentasController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//ruta categorias
Route::get('/categorias',[CategoriaController::class, 'index']) ->name('categorias');
Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store'); 
Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
Route::get('/categorias/{categoria}', [CategoriaController::class , 'show'])->name('categorias.show');
//ruta clientes
Route::get('/clientes',[ClientesController::class, 'index']) ->name('clientes');
Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store'); 
Route::delete('/clientes/{cliente}', [ClientesController::class, 'destroy'])->name('clientes.destroy');
Route::put('/clientes/{cliente}', [ClientesController::class, 'update'])->name('clientes.update');
Route::get('/clientes/{cliente}', [ClientesController::class , 'show'])->name('clientes.show');
//ruta Productos
Route::get('/productos',[ProductosController::class, 'index']) ->name('productos');
Route::post('/productos', [ProductosController::class, 'store'])->name('productos.store'); 
Route::delete('/productos/{producto}', [ProductosController::class, 'destroy'])->name('productos.destroy');
Route::put('/productos/{producto}', [ProductosController::class, 'update'])->name('productos.update');
Route::get('/productos/{producto}', [ProductosController::class , 'show'])->name('productos.show');
//Proveedores rutas
Route::get('/proveedores',[ProveedoresController::class, 'index']) ->name('proveedores');
Route::post('/proveedores', [ProveedoresController::class, 'store'])->name('proveedores.store'); 
Route::delete('/proveedores/{proveedor}', [ProveedoresController::class, 'destroy'])->name('proveedores.destroy');
Route::put('/proveedores/{proveedor}', [ProveedoresController::class, 'update'])->name('proveedores.update');
Route::get('/proveedores/{proveedor}', [ProveedoresController::class , 'show'])->name('proveedores.show');
//Ventas rutas
Route::get('/ventas',[VentasController::class, 'index']) ->name('ventas');
Route::post('/ventas', [VentasController::class, 'store'])->name('ventas.store'); 
Route::delete('/ventas/{venta}', [VentasController::class, 'destroy'])->name('ventas.destroy');
Route::put('/ventas/{venta}', [VentasController::class, 'update'])->name('ventas.update');
Route::get('/ventas/{venta}', [VentasController::class , 'show'])->name('ventas.show');



