# 📊 Diagrama de Flujo del Sistema

## 🔄 Flujo de Creación de Producto con Imagen

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Usuario selecciona imagen de su ordenador               │
│  2. Completa formulario con datos del producto              │
│  3. Envía FormData (multipart/form-data)                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
                   POST /api/admin/products
                    (con Authorization header)
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  SERVIDOR LARAVEL (Backend)                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Middleware: Valida token (auth:sanctum)                 │
│  2. Middleware: Valida rol (role:admin)                     │
│  3. Controller: AdminProductController@store()              │
│  4. Validación: Chequea todos los campos                    │
│     ├─ name (requerido, string)                             │
│     ├─ slug (requerido, único)                              │
│     ├─ price (requerido, numérico)                          │
│     ├─ stock (requerido, entero)                            │
│     ├─ category_id (requerido, existe)                      │
│     └─ image (opcional, archivo válido)                     │
│  5. Si hay imagen:                                          │
│     ├─ Genera UUID único                                    │
│     ├─ Genera nombre: products/uuid.ext                     │
│     ├─ Guarda en storage/app/public/products/               │
│     └─ Almacena ruta en BD                                  │
│  6. Crea registro en tabla products                         │
│  7. Retorna respuesta JSON con datos                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
                        JSON Response
                    (con imagen path)
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Recibe respuesta exitosa                                │
│  2. Imagen disponible en: /storage/products/uuid.jpg        │
│  3. Puede mostrar en <img src="/storage/...">               │
│  4. Producto listo para vender                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Actualización de Producto (Cambiar Imagen)

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Abre formulario de edición                              │
│  2. Selecciona una NUEVA imagen                             │
│  3. Envía PUT con FormData                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    PUT /api/admin/products/1
                    (con archivo nuevo)
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  SERVIDOR LARAVEL (Backend)                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Autenticación y validación OK                           │
│  2. Busca producto en BD                                    │
│  3. Si existe nueva imagen:                                 │
│     ├─ Identifica imagen ANTERIOR                           │
│     ├─ Elimina archivo anterior del servidor                │
│     │  (storage/app/public/products/uuid-viejo.jpg)         │
│     ├─ Guarda NUEVA imagen                                  │
│     │  (storage/app/public/products/uuid-nuevo.jpg)         │
│     └─ Actualiza BD con nueva ruta                          │
│  4. Actualiza otros campos si existen                       │
│  5. Retorna respuesta con datos actualizados                │
└─────────────────────────────────────────────────────────────┘
                            ↓
                Respuesta con nueva imagen
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Imagen anterior: ELIMINADA del servidor                 │
│  2. Imagen nueva: Accesible en /storage/products/uuid.jpg   │
│  3. Producto actualizado exitosamente                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗑️ Flujo de Eliminación de Producto

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Hace click en "Eliminar"                                │
│  2. Confirma la acción                                      │
│  3. Envía DELETE /api/admin/products/1                      │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    DELETE /api/admin/products/1
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  SERVIDOR LARAVEL (Backend)                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Autenticación y validación OK                           │
│  2. Busca producto en BD                                    │
│  3. Si tiene imagen:                                        │
│     └─ Elimina archivo del servidor                         │
│        (storage/app/public/products/uuid.jpg)               │
│  4. Elimina registro de la BD                               │
│  5. Retorna respuesta de confirmación                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
            Producto y su imagen eliminados
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (Frontend)                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  1. Producto eliminado de la base de datos                  │
│  2. Imagen eliminada del servidor                           │
│  3. Espacio en el servidor liberado                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Estructura de Almacenamiento

```
proyecto/
├── storage/
│   └── app/
│       └── public/          ← ¡AQUÍ SE GUARDAN LAS IMÁGENES!
│           ├── products/
│           │   ├── 550e8400-e29b-41d4-a716-446655440000.jpg
│           │   ├── 660e8400-e29b-41d4-a716-446655440001.jpg
│           │   └── 770e8400-e29b-41d4-a716-446655440002.jpg
│           └── categories/
│               ├── 880e8400-e29b-41d4-a716-446655440003.jpg
│               └── 990e8400-e29b-41d4-a716-446655440004.jpg
└── public/
    └── storage/            ← ENLACE SIMBÓLICO (php artisan storage:link)
        ├── products/       ← Accesible vía /storage/products/
        └── categories/     ← Accesible vía /storage/categories/
```

---

## 🔐 Flujo de Autenticación y Autorización

```
SOLICITUD ENTRANTE
        ↓
┌──────────────────────────────────────────┐
│ ¿Tiene header Authorization?             │
└──────────────────────────────────────────┘
        ↓
    NO  → Requiere autenticación (401)
        ↓
    SI  → Valida token (Sanctum)
            ↓
            ¿Token válido?
            ↓
        NO  → Unauthorized (401)
            ↓
        SI  → ¿Es ruta /api/admin/?
                ↓
            NO  → Acceso permitido ✅
            ↓
            SI  → ¿Usuario es admin?
                    ↓
                NO  → Forbidden (403)
                ↓
                SI  → Acceso permitido ✅
                    ↓
                Ejecuta controlador
                    ↓
                Retorna respuesta JSON
```

---

## 📊 Modelo de Datos - Relaciones

```
┌─────────────────────┐
│    CATEGORIES       │
├─────────────────────┤
│ id (PK)             │
│ name                │
│ slug                │
│ description         │
│ image               │ ──────────┐
│ created_at          │           │
│ updated_at          │           │
└─────────────────────┘           │
        ▲                          │
        │ 1:N                      │
        │                          │
┌─────────────────────┐           │
│    PRODUCTS         │           │
├─────────────────────┤           │
│ id (PK)             │           │
│ name                │           │
│ slug                │           │
│ description         │           │
│ price               │           │
│ cost                │           │
│ stock               │           │
│ category_id (FK) ◄──┼───────────┘
│ image               │
│ active              │
│ created_at          │
│ updated_at          │
└─────────────────────┘

Relación:
  • Una CATEGORÍA tiene muchos PRODUCTOS
  • Un PRODUCTO pertenece a una CATEGORÍA
```

---

## 🎯 Validaciones en Cada Paso

```
POST /api/admin/products
        ↓
┌─────────────────────────────────────────┐
│ 1. ¿Token presente? (Middleware)        │ → NO → 401
│ 2. ¿Token válido? (Sanctum)             │ → NO → 401
│ 3. ¿Usuario es admin? (Middleware)      │ → NO → 403
│ 4. ¿name es string? (Validation)        │ → NO → 422
│ 5. ¿slug es único? (Validation)         │ → NO → 422
│ 6. ¿price es numérico? (Validation)     │ → NO → 422
│ 7. ¿category_id existe? (Validation)    │ → NO → 422
│ 8. ¿imagen es válida? (Validation)      │ → NO → 422
│                                         │
│ TODAS LAS VALIDACIONES: OK ✅           │
└─────────────────────────────────────────┘
        ↓
  Guardar en BD
        ↓
  Procesar imagen
        ↓
  Retornar 201 + JSON
```

---

## 📲 Comunicación Cliente-Servidor (Detallada)

### REQUEST (Cliente → Servidor)
```http
POST /api/admin/products HTTP/1.1
Host: localhost:8000
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data; boundary=----WebKitFormBoundary

------WebKitFormBoundary
Content-Disposition: form-data; name="name"

Laptop Gaming
------WebKitFormBoundary
Content-Disposition: form-data; name="slug"

laptop-gaming
------WebKitFormBoundary
Content-Disposition: form-data; name="price"

1500.00
------WebKitFormBoundary
Content-Disposition: form-data; name="stock"

5
------WebKitFormBoundary
Content-Disposition: form-data; name="category_id"

1
------WebKitFormBoundary
Content-Disposition: form-data; name="image"; filename="laptop.jpg"
Content-Type: image/jpeg

[DATOS BINARIOS DE LA IMAGEN]
------WebKitFormBoundary--
```

### RESPONSE (Servidor → Cliente)
```http
HTTP/1.1 201 Created
Content-Type: application/json

{
    "success": true,
    "message": "Producto creado exitosamente",
    "data": {
        "id": 1,
        "name": "Laptop Gaming",
        "slug": "laptop-gaming",
        "price": "1500.00",
        "stock": 5,
        "category_id": 1,
        "image": "products/550e8400-e29b-41d4-a716-446655440000.jpg",
        "active": true,
        "created_at": "2026-05-29T10:30:00.000000Z",
        "updated_at": "2026-05-29T10:30:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electrónica",
            "slug": "electronica"
        }
    }
}
```

---

## 🧠 Lógica de Almacenamiento de Imágenes

```
CUANDO GUARDAS UNA IMAGEN:
┌─────────────────────────────────────────┐
│ 1. Usuario selecciona: laptop.jpg       │
│ 2. Laravel genera UUID: 550e8400-...    │
│ 3. Extensión se preserva: .jpg          │
│ 4. Ruta final: products/550e8400....jpg │
│ 5. Ubicación: /storage/app/public/      │
│                  products/550e8400....jpg│
│ 6. URL pública: /storage/               │
│                  products/550e8400....jpg│
└─────────────────────────────────────────┘

VENTAJAS:
✅ UUID evita colisiones de nombres
✅ Rutas predecibles (products/ o categories/)
✅ Fácil limpiar imágenes huérfanas
✅ Seguro contra ataques
✅ Escalable a S3 o CDN
```

---

## ✅ Checklist de Características

- [x] Crear productos con imagen
- [x] Crear categorías con imagen
- [x] Editar productos (cambiar imagen)
- [x] Editar categorías (cambiar imagen)
- [x] Eliminar productos (limpiar imagen)
- [x] Eliminar categorías (limpiar imagen)
- [x] Validación de archivos
- [x] Validación de campos
- [x] Autenticación requerida
- [x] Autorización (admin)
- [x] Respuestas JSON consistentes
- [x] Manejo de errores
- [x] Documentación completa

**TODO COMPLETADO ✅**

---

*Diagrama creado: 29 de Mayo de 2026*
