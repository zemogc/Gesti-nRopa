# Gestión de Ropa — API REST

API REST construida con **Laravel 11** para administrar el inventario y las ventas de una tienda de ropa: categorías, productos, clientes, proveedores y ventas.

## Características

- CRUD completo para categorías, productos, clientes, proveedores y ventas.
- Validación de datos con Form Requests (errores `422` en JSON).
- Respuestas `404` en JSON cuando un recurso no existe.
- **Control de stock en ventas**: al registrar una venta se descuenta el stock del producto; al editarla o anularla se devuelve. Todo ocurre dentro de una transacción y se rechaza la venta si no hay unidades suficientes.
- El **total de la venta se calcula en el servidor** a partir del precio del producto.
- Filtros de búsqueda en productos, clientes y ventas.
- Endpoint de **resumen** con indicadores del negocio.
- Seeder con un catálogo de ropa de ejemplo.
- Tests de integración para todos los endpoints.

## Requisitos

- PHP 8.2 o superior (extensiones `pdo_sqlite` o `pdo_mysql`)
- Composer 2

## Instalación

```bash
git clone https://github.com/zemogc/Gesti-nRopa.git
cd Gesti-nRopa
composer install
cp .env.example .env
php artisan key:generate
```

Por defecto se usa SQLite. Crea la base y carga los datos de ejemplo:

```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

La API queda disponible en `http://localhost:8000/api`.

Para usar MySQL, ajusta en `.env` las variables `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

## Endpoints

Todas las rutas tienen el prefijo `/api`. Cada recurso expone las operaciones REST estándar:

| Método | Ruta | Acción |
| --- | --- | --- |
| `GET` | `/{recurso}` | Listar |
| `POST` | `/{recurso}` | Crear (`201`) |
| `GET` | `/{recurso}/{id}` | Ver detalle |
| `PUT` / `PATCH` | `/{recurso}/{id}` | Actualizar |
| `DELETE` | `/{recurso}/{id}` | Eliminar |

Recursos: `categorias`, `productos`, `clientes`, `proveedores`, `ventas`.

Adicionalmente: `GET /resumen` devuelve indicadores generales.

### Campos y validaciones

| Recurso | Campos |
| --- | --- |
| Categorías | `nombre` (único, máx. 50), `descripcion` |
| Productos | `nombre`, `descripcion`, `precio` (≥ 0), `categoria_id` (debe existir), `stock` (entero ≥ 0) |
| Clientes | `nombre`, `apellido`, `email` (único), `telefono` (dígitos, espacios, `+`, `-`, `()`) |
| Proveedores | `nombre`, `contacto` |
| Ventas | `producto_id`, `cliente_id`, `cantidad` (≥ 1), `fecha_venta` (opcional, no futura; por defecto hoy) |

En ventas el campo `total` **no se envía**: lo calcula la API.

### Filtros

| Ruta | Parámetros |
| --- | --- |
| `GET /productos` | `buscar`, `categoria_id`, `stock_bajo` (ej. `stock_bajo=5`) |
| `GET /clientes` | `buscar` (nombre, apellido o email) |
| `GET /ventas` | `cliente_id`, `producto_id`, `desde`, `hasta` (`YYYY-MM-DD`) |
| `GET /resumen` | `stock_minimo` (por defecto `5`) |

### Ejemplos

Registrar una venta:

```bash
curl -X POST http://localhost:8000/api/ventas \
  -H "Content-Type: application/json" \
  -d '{"producto_id": 1, "cliente_id": 1, "cantidad": 2}'
```

```json
{
  "venta": {
    "id": 21,
    "producto_id": 1,
    "cliente_id": 1,
    "cantidad": 2,
    "fecha_venta": "2026-10-28",
    "total": "70000.00",
    "producto_nombre": "Camiseta basica algodon",
    "cliente_nombre": "Ana",
    "cliente_apellido": "Gomez"
  }
}
```

Si no hay stock suficiente:

```json
{
  "message": "Stock insuficiente para Camiseta basica algodon. Disponible: 1.",
  "errors": { "cantidad": ["Stock insuficiente para Camiseta basica algodon. Disponible: 1."] }
}
```

## Estructura

```
app/
├── Http/
│   ├── Controllers/api/   # Controladores REST y ResumenController
│   ├── Requests/          # Reglas de validación por recurso
│   └── Resources/         # Formato JSON de productos y ventas
├── Models/                # Categoria, Producto, Cliente, Proveedor, Venta
└── Services/
    └── VentaService.php   # Lógica de ventas y stock
database/
├── factories/
├── migrations/
└── seeders/
tests/Feature/             # Tests de integración de la API
```

## Tests y estilo

```bash
composer test   # PHPUnit (SQLite en memoria)
composer lint   # Laravel Pint
```

## Licencia

MIT
