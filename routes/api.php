<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ProductosController;
use App\Http\Controllers\api\CategoriasController;
use App\Http\Controllers\api\ClientesController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Producto rutas
Route::get('/productos',[ProductosController::class, 'index']) ->name('productos');

//Categorias rutas
Route::get('/categorias',[CategoriasController::class, 'index']) ->name('categorias');

//Clientes rutas
Route::get('/clientes',[ClientesController::class, 'index']) ->name('clientes');

//Proveedores rutas

//Ventas rutas



