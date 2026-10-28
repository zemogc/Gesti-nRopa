# Gestión de Ropa — API REST

API REST construida con **Laravel 11** para administrar el inventario y las ventas de una tienda de ropa: categorías, productos, clientes, proveedores y ventas.

## Características

- CRUD completo para categorías, productos, clientes, proveedores y ventas.
- Validación de datos con Form Requests y **mensajes en español** (errores `422` en JSON).
- Respuestas `404` en JSON cuando un recurso no existe.
- **Control de stock en ventas**: al registrar una venta se descuenta el stock del producto; al editarla o anularla se devuelve. Todo ocurre dentro de una transacción y se rechaza la venta si no hay unidades suficientes.
- El **total de la venta se calcula en el servidor** a partir del precio del producto.
- **Protección del historial**: no se pueden eliminar clientes o productos con ventas, ni categorías con productos (`409`).
- Productos asociados a un **proveedor** y endpoint de **reabastecimiento** de stock.
- Filtros de búsqueda y **paginación opcional** en los listados.
- Endpoint de **resumen** con indicadores del negocio.
- **Autenticación opcional** con tokens (Laravel Sanctum) y límite de peticiones por minuto.
- Entorno **Docker** con MySQL listo para usar.
- Seeder con un catálogo de ropa de ejemplo.
- Tests de integración para todos los endpoints.

## Inicio rápido con Docker

Solo necesitas Docker:

```bash
git clone https://github.com/zemogc/Gesti-nRopa.git
cd Gesti-nRopa
docker compose up -d --build
```

La API queda en `http://localhost:8000/api` con MySQL y datos de ejemplo cargados. MySQL se expone en el puerto `3307` (usuario `gestion`, contraseña `gestion`).

Comandos útiles:

```bash
docker compose exec app composer test               # tests
docker compose exec app php artisan migrate:fresh --seed  # reiniciar datos
docker compose down                                 # detener
```

## Instalación sin Docker

Requisitos: PHP 8.2 o superior (extensiones `pdo_sqlite` o `pdo_mysql`) y Composer 2.

```bash
git clone https://github.com/zemogc/Gesti-nRopa.git
cd Gesti-nRopa
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Por defecto se usa SQLite. Para usar MySQL, ajusta en `.env` las variables `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

## Configuración

| Variable | Por defecto | Descripción |
| --- | --- | --- |
| `API_REQUIERE_AUTH` | `false` | Si es `true`, todos los endpoints exigen un token Bearer. |
| `API_LIMITE_POR_MINUTO` | `120` | Peticiones por minuto por usuario o IP; al superarlo se responde `429`. |

## Autenticación

Crea un usuario y obtén un token:

```bash
php artisan usuario:crear "Admin" admin@tienda.com
```

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@tienda.com", "password": "tu-contrasena"}'
```

```json
{ "token": "1|abc...", "tipo": "Bearer", "usuario": { "id": 1, "name": "Admin" } }
```

Envía el token en cada petición con `Authorization: Bearer <token>`. Para revocarlo: `POST /api/auth/logout`. El login admite 5 intentos por minuto.

El seeder crea el usuario `test@example.com` con contraseña `password`.

## Endpoints

Todas las rutas tienen el prefijo `/api`. Cada recurso expone las operaciones REST estándar:

| Método | Ruta | Acción |
| --- | --- | --- |
| `GET` | `/{recurso}` | Listar |
| `POST` | `/{recurso}` | Crear (`201`) |
| `GET` | `/{recurso}/{id}` | Ver detalle |
| `PUT` / `PATCH` | `/{recurso}/{id}` | Actualizar |
| `DELETE` | `/{recurso}/{id}` | Eliminar (`409` si tiene registros asociados) |

Recursos: `categorias`, `productos`, `clientes`, `proveedores`, `ventas`.

Además:

| Método | Ruta | Acción |
| --- | --- | --- |
| `POST` | `/productos/{id}/reabastecer` | Suma `cantidad` al stock; opcionalmente registra `proveedor_id` |
| `GET` | `/resumen` | Indicadores: totales, ingresos, stock bajo y más vendidos |
| `POST` | `/auth/login` | Obtener token |
| `POST` | `/auth/logout` | Revocar token |
| `GET` | `/user` | Usuario autenticado |

En [docs/api.http](docs/api.http) hay peticiones de ejemplo listas para la extensión REST Client de VS Code.

### Campos y validaciones

| Recurso | Campos |
| --- | --- |
| Categorías | `nombre` (único, máx. 50), `descripcion` |
| Productos | `nombre`, `descripcion`, `precio` (≥ 0), `categoria_id` (debe existir), `proveedor_id` (opcional), `stock` (entero ≥ 0) |
| Clientes | `nombre`, `apellido`, `email` (único), `telefono` (dígitos, espacios, `+`, `-`, `()`) |
| Proveedores | `nombre`, `contacto` |
| Ventas | `producto_id`, `cliente_id`, `cantidad` (≥ 1), `fecha_venta` (opcional, no futura; por defecto hoy) |

En ventas el campo `total` **no se envía**: lo calcula la API.

### Filtros y paginación

| Ruta | Parámetros |
| --- | --- |
| `GET /productos` | `buscar`, `categoria_id`, `proveedor_id`, `stock_bajo` (ej. `stock_bajo=5`) |
| `GET /clientes` | `buscar` (nombre, apellido o email) |
| `GET /ventas` | `cliente_id`, `producto_id`, `desde`, `hasta` (`YYYY-MM-DD`) |
| `GET /resumen` | `stock_minimo` (por defecto `5`) |

Todos los listados aceptan `por_pagina` (máx. 100) y `pagina`. Sin `por_pagina` se devuelve la lista completa. Con paginación se agrega:

```json
"paginacion": { "pagina_actual": 1, "por_pagina": 10, "total": 42, "ultima_pagina": 5 }
```

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
│   ├── Controllers/api/   # Controladores REST, resumen y autenticación
│   ├── Middleware/        # Autenticación opcional
│   ├── Requests/          # Reglas de validación por recurso
│   └── Resources/         # Formato JSON de productos y ventas
├── Models/                # Categoria, Producto, Cliente, Proveedor, Venta
└── Services/
    └── VentaService.php   # Lógica de ventas y stock
config/gestion.php         # Opciones propias de la API
database/
├── factories/
├── migrations/
└── seeders/
docker/                    # Script de arranque del contenedor
docs/api.http              # Peticiones de ejemplo
lang/es/                   # Mensajes de validación en español
tests/Feature/             # Tests de integración de la API
```

## Tests y estilo

```bash
composer test   # PHPUnit (siempre usa SQLite en memoria, también dentro de Docker)
composer lint   # Laravel Pint
```

## Licencia

MIT
