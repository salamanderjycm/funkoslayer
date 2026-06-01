# 🧪 Ejemplos de Test con cURL

## Prerrequisitos

```bash
# 1. Obtén el token primero
TOKEN="tu_token_aqui"

# 2. Reemplaza estos valores
API_URL="http://localhost:8000"
EMAIL="admin@example.com"
PASSWORD="password"
```

---

## 1️⃣ Obtener Token (Importante: Primero!)

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Respuesta esperada:
# {
#   "success": true,
#   "data": {
#     "user": {...},
#     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
#   }
# }

# GUARDA EL TOKEN EN UNA VARIABLE:
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."
```

---

## 2️⃣ Crear Categoría con Imagen

```bash
# Asegúrate que tienes una imagen en: /ruta/a/imagen.jpg
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Electrónica" \
  -F "slug=electronica" \
  -F "description=Todos los productos electrónicos" \
  -F "image=@/ruta/a/imagen.jpg"

# Respuesta esperada (201):
# {
#   "success": true,
#   "message": "Categoría creada exitosamente",
#   "data": {
#     "id": 1,
#     "name": "Electrónica",
#     "slug": "electronica",
#     "image": "categories/550e8400-e29b-41d4-a716-446655440000.jpg"
#   }
# }
```

### Variantes de Categorías

```bash
# Categoría SIN imagen
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Ropa" \
  -F "slug=ropa" \
  -F "description=Ropa y accesorios"

# Categoría con descripción larga
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Libros" \
  -F "slug=libros" \
  -F "description=Libros de ficción, no ficción y técnicos" \
  -F "image=@/ruta/a/libro.jpg"
```

---

## 3️⃣ Ver Todas las Categorías (Público - Sin token)

```bash
curl -X GET http://localhost:8000/api/categories

# Respuesta esperada (200):
# {
#   "success": true,
#   "data": [
#     {
#       "id": 1,
#       "name": "Electrónica",
#       "slug": "electronica",
#       "image": "categories/550e8400-e29b-41d4-a716-446655440000.jpg",
#       "products": []
#     }
#   ]
# }
```

---

## 4️⃣ Ver una Categoría Específica (Público)

```bash
curl -X GET http://localhost:8000/api/categories/1

# Respuesta esperada (200):
# {
#   "success": true,
#   "data": {
#     "id": 1,
#     "name": "Electrónica",
#     "products": [...]
#   }
# }
```

---

## 5️⃣ Actualizar Categoría (Cambiar imagen)

```bash
# Cambiar solo nombre
curl -X PUT http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Electrónica y Accesorios"

# Cambiar nombre E imagen
curl -X PUT http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Electrónica" \
  -F "image=@/ruta/a/imagen-nueva.jpg"

# Cambiar descripción solamente
curl -X PUT http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "description=Descripción actualizada"
```

---

## 6️⃣ Eliminar Categoría (Elimina imagen también)

```bash
curl -X DELETE http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer $TOKEN"

# Respuesta esperada (200):
# {
#   "success": true,
#   "message": "Categoría eliminada exitosamente"
# }
```

---

## 7️⃣ Crear Producto con Imagen

```bash
# Primero necesitas el category_id (del paso anterior = 1)
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Laptop Gaming RTX 4090" \
  -F "slug=laptop-gaming-rtx-4090" \
  -F "description=Laptop de última generación con RTX 4090" \
  -F "price=2500.00" \
  -F "cost=1800.00" \
  -F "stock=5" \
  -F "category_id=1" \
  -F "image=@/ruta/a/laptop.jpg"

# Respuesta esperada (201):
# {
#   "success": true,
#   "message": "Producto creado exitosamente",
#   "data": {
#     "id": 1,
#     "name": "Laptop Gaming RTX 4090",
#     "price": "2500.00",
#     "stock": 5,
#     "image": "products/550e8400-e29b-41d4-a716-446655440001.jpg"
#   }
# }
```

### Variantes de Productos

```bash
# Producto SIN imagen
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Mouse Genérico" \
  -F "slug=mouse-generico" \
  -F "price=25.00" \
  -F "stock=100" \
  -F "category_id=1"

# Producto sin costo (costo es opcional)
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Monitor 4K" \
  -F "slug=monitor-4k" \
  -F "price=599.99" \
  -F "stock=10" \
  -F "category_id=1" \
  -F "image=@/ruta/a/monitor.jpg"

# Producto inactivo (se guarda pero no aparece en /api/products público)
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Producto Futuro" \
  -F "slug=producto-futuro" \
  -F "price=999.99" \
  -F "stock=0" \
  -F "category_id=1" \
  -F "active=false"
```

---

## 8️⃣ Ver Todos los Productos (Público - Solo activos)

```bash
curl -X GET http://localhost:8000/api/products

# Respuesta esperada (200):
# {
#   "success": true,
#   "data": [
#     {
#       "id": 1,
#       "name": "Laptop Gaming RTX 4090",
#       "price": "2500.00",
#       "stock": 5,
#       "image": "products/550e8400-e29b-41d4-a716-446655440001.jpg",
#       "category": {...}
#     }
#   ]
# }
```

---

## 9️⃣ Ver Todos los Productos (Admin - Incluyendo inactivos)

```bash
curl -X GET http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN"

# Retorna TODOS los productos, incluyendo active: false
```

---

## 🔟 Ver Producto Específico (Público)

```bash
curl -X GET http://localhost:8000/api/products/1

# Respuesta esperada (200):
# {
#   "success": true,
#   "data": {
#     "id": 1,
#     "name": "Laptop Gaming RTX 4090",
#     "price": "2500.00",
#     "stock": 5,
#     "image": "products/550e8400-e29b-41d4-a716-446655440001.jpg",
#     "category": {...}
#   }
# }
```

---

## 1️⃣1️⃣ Actualizar Producto (Cambiar imagen)

```bash
# Cambiar solo precio
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "price=2299.99"

# Cambiar precio Y imagen
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "price=2399.99" \
  -F "image=@/ruta/a/laptop-nueva.jpg"

# Cambiar stock
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "stock=10"

# Cambiar a inactivo
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "active=false"

# Cambiar múltiples campos
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Laptop Gaming RTX 4090 Ti" \
  -F "price=3000.00" \
  -F "stock=3" \
  -F "image=@/ruta/a/laptop-nueva.jpg"
```

---

## 1️⃣2️⃣ Eliminar Producto (Elimina imagen también)

```bash
curl -X DELETE http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer $TOKEN"

# Respuesta esperada (200):
# {
#   "success": true,
#   "message": "Producto eliminado exitosamente"
# }
```

---

## ❌ Errores Comunes (y sus soluciones)

### Error 401: Unauthorized

```bash
# ❌ INCORRECTO - Sin token
curl -X POST http://localhost:8000/api/admin/products \
  -F "name=Producto"

# ✅ CORRECTO - Con token
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Producto"
```

### Error 403: Forbidden

```bash
# Significa que tu usuario no es admin
# Solución en la base de datos:
# UPDATE users SET role = 'admin' WHERE id = 1;
```

### Error 422: Validation Error

```bash
# ❌ Campo requerido faltante
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Producto"
  # Falta: slug, price, stock, category_id

# ✅ Todos los campos requeridos
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Producto" \
  -F "slug=producto" \
  -F "price=99.99" \
  -F "stock=10" \
  -F "category_id=1"
```

### Error: Imagen no se guarda

```bash
# ❌ Enviando como JSON
curl -X POST http://localhost:8000/api/admin/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"image": "datos.jpg"}' # INCORRECTO

# ✅ Enviando como multipart/form-data
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "image=@/ruta/a/imagen.jpg" # CORRECTO
```

---

## 💾 Script Bash Completo (Test Rápido)

```bash
#!/bin/bash

# ============================================
# Test Completo de API de Productos
# ============================================

API_URL="http://localhost:8000"
EMAIL="admin@example.com"
PASSWORD="password"

echo "1. Obteniendo token..."
LOGIN=$(curl -s -X POST $API_URL/api/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "✅ Token: $TOKEN"

echo -e "\n2. Creando categoría..."
CATEGORY=$(curl -s -X POST $API_URL/api/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Electrónica" \
  -F "slug=electronica" \
  -F "description=Productos electrónicos" \
  -F "image=@imagen.jpg")

CATEGORY_ID=$(echo $CATEGORY | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
echo "✅ Categoría creada con ID: $CATEGORY_ID"

echo -e "\n3. Creando producto..."
PRODUCT=$(curl -s -X POST $API_URL/api/admin/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Laptop" \
  -F "slug=laptop" \
  -F "price=1500.00" \
  -F "stock=5" \
  -F "category_id=$CATEGORY_ID" \
  -F "image=@imagen.jpg")

PRODUCT_ID=$(echo $PRODUCT | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
echo "✅ Producto creado con ID: $PRODUCT_ID"

echo -e "\n4. Listando productos públicos..."
curl -s -X GET $API_URL/api/products | jq .

echo -e "\n✅ ¡Test completado exitosamente!"
```

---

## 📱 Guardar Token en Variable de Entorno

```bash
# Opción 1: Guardar en variable local
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | grep -o '"token":"[^"]*' | cut -d'"' -f4)

echo "Token guardado: $TOKEN"

# Usarlo luego:
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/admin/products

# Opción 2: En archivo .env local (NO en repositorio!)
echo "TOKEN=tu_token_aqui" > .env.local
source .env.local
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/admin/products
```

---

## 🔍 Ver Respuesta Formateada

```bash
# Instala jq primero: apt-get install jq
# Luego agrega | jq al final:

curl -X GET http://localhost:8000/api/products | jq .

# O para ver solo ciertos campos:
curl -X GET http://localhost:8000/api/products | jq '.data[].name'

# O con colores:
curl -X GET http://localhost:8000/api/products | jq -C .
```

---

## 📝 Nota sobre Imágenes de Prueba

Para las pruebas necesitas imágenes reales. Puedes usar:

```bash
# Descargar imagen de ejemplo
curl -o imagen.jpg https://via.placeholder.com/300

# O crear una imagen simple con ImageMagick
convert -size 300x300 xc:blue imagen.jpg

# O usar una imagen que ya tengas en tu PC:
curl -F "image=@/ruta/real/a/tu/imagen.jpg" ...
```

---

*Scripts de test cURL - Actualizado: 29 de Mayo de 2026*
