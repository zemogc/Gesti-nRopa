<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    public const INICIAL = 'inicial';

    public const VENTA = 'venta';

    public const EDICION_VENTA = 'edicion_venta';

    public const ANULACION = 'anulacion';

    public const REABASTECIMIENTO = 'reabastecimiento';

    public const AJUSTE = 'ajuste';

    public const TIPOS = [
        self::INICIAL,
        self::VENTA,
        self::EDICION_VENTA,
        self::ANULACION,
        self::REABASTECIMIENTO,
        self::AJUSTE,
    ];

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'stock_resultante',
        'venta_id',
        'proveedor_id',
        'user_id',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'producto_id' => 'integer',
            'cantidad' => 'integer',
            'stock_resultante' => 'integer',
            'venta_id' => 'integer',
            'proveedor_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
