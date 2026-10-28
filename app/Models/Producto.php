<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria_id',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock' => 'integer',
            'categoria_id' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Productos con stock igual o menor al limite indicado.
     */
    public function scopeStockBajo(Builder $query, int $limite = 5): Builder
    {
        return $query->where('stock', '<=', $limite);
    }
}
