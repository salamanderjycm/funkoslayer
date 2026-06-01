# 📚 Documentación API - Ecommerce

## 📁 Gestión de Categorías

### 1. Listar todas las categorías
```http
GET /api/categories
```
**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Electrónica",
            "slug": "electronica",
            "description": "Productos electrónicos diversos",
            "image": "categories/uuid.jpg",
            "created_at": "2026-05-29T...",
            "updated_at": "2026-05-29T...",
            "products": [...]
        }
    ]
}
```

### 2. Obtener una categoría específica
```http
GET /api/categories/{id}
```

### 3. Crear nueva categoría (Admin)
```http
POST /api/admin/categories
Content-Type: multipart/form-data

{
    "name": "Electrónica",
    "slug": "electronica",
    "description": "Productos electrónicos",
    "image": <archivo de imagen>
}
```

**Validaciones:**
- `name`: Requerido, único, máx 255 caracteres
- `slug`: Requerido, único, máx 255 caracteres
- `description`: Opcional, texto
- `image`: Opcional, imagen (JPEG, PNG, JPG, GIF, WebP), máx 2MB

**Respuesta:**
```json
{
    "success": true,
    "message": "Categoría creada exitosamente",
    "data": {...}
}
```

### 4. Actualizar categoría (Admin)
```http
PUT /api/admin/categories/{id}
Content-Type: multipart/form-data

{
    "name": "Electrónica",
    "slug": "electronica",
    "description": "Descripción actualizada",
    "image": <archivo de imagen (opcional)>
}
```

**Nota:** Si envías una nueva imagen, la anterior se eliminará automáticamente del servidor.

### 5. Eliminar categoría (Admin)
```http
DELETE /api/admin/categories/{id}
```

La imagen asociada se elimina automáticamente.

---

## 🛍️ Gestión de Productos

### 1. Listar productos activos (Público)
```http
GET /api/products
```
Retorna solo productos con `active: true`

### 2. Obtener producto específico (Público)
```http
GET /api/products/{id}
```

### 3. Listar todos los productos (Admin)
```http
GET /api/admin/products
```
Retorna todos los productos incluyendo inactivos

### 4. Crear nuevo producto (Admin)
```http
POST /api/admin/products
Content-Type: multipart/form-data

{
    "name": "Laptop Gaming",
    "slug": "laptop-gaming",
    "description": "Laptop de alta performance",
    "price": 1500.00,
    "cost": 1000.00,
    "stock": 10,
    "category_id": 1,
    "image": <archivo de imagen>,
    "active": true
}
```

**Validaciones:**
- `name`: Requerido, string, máx 255 caracteres
- `slug`: Requerido, único, string, máx 255 caracteres
- `description`: Opcional, texto
- `price`: Requerido, numérico, mínimo 0
- `cost`: Opcional, numérico, mínimo 0
- `stock`: Requerido, entero, mínimo 0
- `category_id`: Requerido, debe existir en la tabla categorías
- `image`: **Opcional, archivo de imagen** (JPEG, PNG, JPG, GIF, WebP), máx 2MB
- `active`: Booleano, por defecto true

**Respuesta:**
```json
{
    "success": true,
    "message": "Producto creado exitosamente",
    "data": {
        "id": 1,
        "name": "Laptop Gaming",
        "slug": "laptop-gaming",
        "price": "1500.00",
        "stock": 10,
        "image": "products/uuid.jpg",
        "category": {...},
        "created_at": "2026-05-29T..."
    }
}
```

### 5. Actualizar producto (Admin)
```http
PUT /api/admin/products/{id}
Content-Type: multipart/form-data

{
    "name": "Laptop Gaming",
    "price": 1400.00,
    "stock": 15,
    "image": <archivo de imagen (opcional)>,
    ...
}
```

**Nota:** Si envías una nueva imagen, la anterior se eliminará automáticamente.

### 6. Eliminar producto (Admin)
```http
DELETE /api/admin/products/{id}
```
La imagen asociada se elimina automáticamente.

---

## 🖼️ Manejo de Imágenes

### Subir imagen
- **Formato:** Multipart form-data
- **Campo:** `image` (tipo file)
- **Tipos permitidos:** JPEG, PNG, JPG, GIF, WebP
- **Tamaño máximo:** 2MB

### Acceder a la imagen
Las imágenes se guardan en `/storage/public/` y se pueden acceder en el frontend:

```javascript
// Si tienes la ruta: "products/uuid.jpg"
// URL completa: /storage/products/uuid.jpg

<img src={'/storage/' + product.image} alt={product.name} />
```

### Rutas de almacenamiento
- **Productos:** `storage/app/public/products/`
- **Categorías:** `storage/app/public/categories/`

---

## 🔐 Autenticación y Autorización

### Rutas públicas (sin autenticación)
- `GET /api/products`
- `GET /api/products/{id}`
- `GET /api/categories`
- `GET /api/categories/{id}`

### Rutas protegidas (requieren token)
- `POST /login`
- `POST /register`

### Rutas de Admin (requieren token + rol admin)
- `GET /api/admin/products`
- `POST /api/admin/products`
- `PUT /api/admin/products/{id}`
- `DELETE /api/admin/products/{id}`
- `GET /api/admin/categories`
- `POST /api/admin/categories`
- `PUT /api/admin/categories/{id}`
- `DELETE /api/admin/categories/{id}`

---

## 📝 Ejemplos con JavaScript/Fetch

### Crear producto con imagen
```javascript
const formData = new FormData();
formData.append('name', 'Mi Producto');
formData.append('slug', 'mi-producto');
formData.append('description', 'Descripción del producto');
formData.append('price', 99.99);
formData.append('cost', 50.00);
formData.append('stock', 10);
formData.append('category_id', 1);
formData.append('image', fileInput.files[0]); // El archivo de imagen

const response = await fetch('/api/admin/products', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    body: formData
});

const result = await response.json();
console.log(result);
```

### Actualizar producto con nueva imagen
```javascript
const formData = new FormData();
formData.append('name', 'Producto Actualizado');
formData.append('price', 149.99);
formData.append('image', fileInput.files[0]); // Nueva imagen (opcional)

const response = await fetch('/api/admin/products/1', {
    method: 'PUT',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    body: formData
});

const result = await response.json();
```

### Crear categoría con imagen
```javascript
const formData = new FormData();
formData.append('name', 'Electrónica');
formData.append('slug', 'electronica');
formData.append('description', 'Productos electrónicos diversos');
formData.append('image', fileInput.files[0]);

const response = await fetch('/api/admin/categories', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    body: formData
});

const result = await response.json();
```

---

## ⚠️ Códigos de respuesta HTTP

| Código | Significado |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Validación fallida |
| 401 | Unauthorized - Requiere autenticación |
| 403 | Forbidden - Sin permisos suficientes |
| 404 | Not Found - Recurso no encontrado |
| 500 | Server Error - Error interno del servidor |

---

## 🎯 Puntos Importantes

1. **Las imágenes se guardan automáticamente** cuando se envía el campo `image`
2. **Las imágenes anteriores se eliminan** cuando se actualiza un producto/categoría
3. **El slug debe ser único** (sin espacios, en minúsculas, separado por guiones)
4. **Las imágenes tienen límite de 2MB**
5. **Solo se aceptan formatos:** JPEG, PNG, JPG, GIF, WebP
6. **Todas las operaciones CRUD necesitan autenticación** (excepto GET públicos)
7. **Las operaciones de admin necesitan rol "admin"**
