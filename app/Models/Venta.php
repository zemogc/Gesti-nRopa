<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'producto_id',
        'cliente_id',
        'cantidad',
        'fecha_venta',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'producto_id' => 'integer',
            'cliente_id' => 'integer',
            'cantidad' => 'integer',
            'fecha_venta' => 'date:Y-m-d',
            'total' => 'decimal:2',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
