<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * decimal(8, 2) solo admite hasta 999.999,99: con precios en pesos una venta de
 * pocas unidades superaba ese valor y MySQL rechazaba el registro.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio', 12, 2)->change();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total', 14, 2)->change();
        });
    }

    /**
     * Reverse the migrations. Falla en MySQL si ya existen valores mayores a 999.999,99.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio', 8, 2)->change();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total', 8, 2)->change();
        });
    }
};
