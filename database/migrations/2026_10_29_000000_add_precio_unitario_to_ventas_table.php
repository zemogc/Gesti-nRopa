<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda el precio con el que se vendio cada unidad, para que editar una venta
 * antigua no la recalcule con el precio actual del producto.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('precio_unitario', 12, 2)->nullable()->after('cantidad');
        });

        DB::table('ventas')
            ->where('cantidad', '>', 0)
            ->update(['precio_unitario' => DB::raw('ROUND(total / cantidad, 2)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('precio_unitario');
        });
    }
};
