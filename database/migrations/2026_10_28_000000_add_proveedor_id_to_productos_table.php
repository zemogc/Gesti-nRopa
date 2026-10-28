<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('proveedor_id')
                ->nullable()
                ->after('categoria_id')
                ->constrained('proveedores')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // SQLite no permite eliminar llaves foraneas; alli basta con quitar la columna
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->dropColumn('proveedor_id');
            } else {
                $table->dropConstrainedForeignId('proveedor_id');
            }
        });
    }
};
