<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id';
     public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria_id',
        'stock',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
