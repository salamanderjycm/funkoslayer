# 🚀 Guía Rápida de Inicio

## ¿Qué se implementó?

✅ **Crear categorías** con imágenes  
✅ **Crear productos** con imágenes  
✅ **Editar y eliminar** categorías y productos  
✅ **Subida de imágenes desde tu ordenador** (no URLs)

---

## ⚡ Uso Rápido

### 1️⃣ Crear una categoría

**Endpoint:** `POST /api/admin/categories`

```javascript
const formData = new FormData();
formData.append('name', 'Electrónica');
formData.append('slug', 'electronica');
formData.append('description', 'Todos los productos electrónicos');
formData.append('image', document.getElementById('fileInput').files[0]);

const response = await fetch('/api/admin/categories', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + tuToken // Tu token de autenticación
    },
    body: formData
});

const data = await response.json();
console.log(data);
// Resultado: { success: true, message: "Categoría creada exitosamente", data: {...} }
```

---

### 2️⃣ Crear un producto con imagen

**Endpoint:** `POST /api/admin/products`

```javascript
const formData = new FormData();
formData.append('name', 'Laptop Gaming RTX 4090');
formData.append('slug', 'laptop-gaming-rtx-4090');
formData.append('description', 'Laptop de última generación con RTX 4090');
formData.append('price', 2500.00);
formData.append('cost', 1800.00);
formData.append('stock', 5);
formData.append('category_id', 1); // El ID de la categoría creada arriba
formData.append('image', document.getElementById('fileInput').files[0]);

const response = await fetch('/api/admin/products', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + tuToken
    },
    body: formData
});

const data = await response.json();
console.log(data);
// Resultado: { success: true, message: "Producto creado exitosamente", data: {...} }
```

---

### 3️⃣ Ver todas las categorías

**Endpoint:** `GET /api/categories` (Público, sin autenticación)

```javascript
const response = await fetch('/api/categories');
const data = await response.json();

console.log(data.data); // Array de categorías
// [{
//   "id": 1,
//   "name": "Electrónica",
//   "image": "categories/uuid-1234.jpg",
//   "products": [...]
// }]
```

---

### 4️⃣ Ver todos los productos

**Endpoint:** `GET /api/products` (Público, solo activos)

```javascript
const response = await fetch('/api/products');
const data = await response.json();

console.log(data.data); // Array de productos
// [{
//   "id": 1,
//   "name": "Laptop Gaming RTX 4090",
//   "price": "2500.00",
//   "image": "products/uuid-5678.jpg",
//   "category": {...}
// }]
```

---

### 5️⃣ Mostrar imagen en HTML

```html
<!-- Dado que tienes "image": "products/uuid-5678.jpg" -->
<img src="/storage/products/uuid-5678.jpg" alt="Producto">

<!-- En Vue/React -->
<img :src="'/storage/' + product.image" :alt="product.name" />
```

---

## 🎨 Ejemplo Completo en React

```jsx
import React, { useState } from 'react';

export function CreateProduct() {
    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        description: '',
        price: '',
        cost: '',
        stock: '',
        category_id: '',
        image: null
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({...prev, [name]: value}));
    };

    const handleImageChange = (e) => {
        setFormData(prev => ({
            ...prev,
            image: e.target.files[0]
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        const form = new FormData();
        form.append('name', formData.name);
        form.append('slug', formData.slug);
        form.append('description', formData.description);
        form.append('price', formData.price);
        form.append('cost', formData.cost);
        form.append('stock', formData.stock);
        form.append('category_id', formData.category_id);
        if (formData.image) {
            form.append('image', formData.image);
        }

        try {
            const response = await fetch('/api/admin/products', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: form
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Producto creado exitosamente');
                // Limpiar formulario o redirigir
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="text"
                name="name"
                placeholder="Nombre del producto"
                value={formData.name}
                onChange={handleChange}
                required
            />
            
            <input
                type="text"
                name="slug"
                placeholder="slug-del-producto"
                value={formData.slug}
                onChange={handleChange}
                required
            />
            
            <textarea
                name="description"
                placeholder="Descripción"
                value={formData.description}
                onChange={handleChange}
            />
            
            <input
                type="number"
                name="price"
                placeholder="Precio"
                step="0.01"
                value={formData.price}
                onChange={handleChange}
                required
            />
            
            <input
                type="number"
                name="cost"
                placeholder="Costo"
                step="0.01"
                value={formData.cost}
                onChange={handleChange}
            />
            
            <input
                type="number"
                name="stock"
                placeholder="Stock"
                value={formData.stock}
                onChange={handleChange}
                required
            />
            
            <select
                name="category_id"
                value={formData.category_id}
                onChange={handleChange}
                required
            >
                <option value="">Selecciona categoría</option>
                <option value="1">Electrónica</option>
                {/* Cargar dinámicamente */}
            </select>
            
            <input
                type="file"
                accept="image/*"
                onChange={handleImageChange}
            />
            
            <button type="submit">Crear Producto</button>
        </form>
    );
}
```

---

## 📝 Formulario HTML Simple

```html
<form method="POST" action="/api/admin/products" enctype="multipart/form-data">
    <h2>Crear Nuevo Producto</h2>
    
    <input type="text" name="name" placeholder="Nombre" required>
    <input type="text" name="slug" placeholder="slug-del-producto" required>
    <textarea name="description" placeholder="Descripción"></textarea>
    
    <input type="number" name="price" step="0.01" placeholder="Precio" required>
    <input type="number" name="cost" step="0.01" placeholder="Costo">
    <input type="number" name="stock" placeholder="Stock" required>
    
    <select name="category_id" required>
        <option>Selecciona categoría</option>
        <option value="1">Electrónica</option>
    </select>
    
    <label>Imagen del producto:</label>
    <input type="file" name="image" accept="image/*">
    
    <button type="submit">Crear Producto</button>
</form>

<script>
    document.querySelector('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        const response = await fetch('/api/admin/products', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            body: formData
        });
        
        const result = await response.json();
        console.log(result);
    });
</script>
```

---

## ⚙️ Configuración Requerida

1. **Base de datos** debe estar migrada:
   ```bash
   php artisan migrate
   ```

2. **Token de autenticación** - Debes tener un token válido:
   ```bash
   # Obtén token haciendo login
   POST /api/login
   {
       "email": "admin@example.com",
       "password": "password"
   }
   ```

3. **Permisos** - Tu usuario debe ser admin:
   - En la BD: `role = 'admin'`

4. **Almacenamiento** está configurado en:
   - `config/filesystems.php`
   - Disco público: `storage/app/public/`
   - URL base: `/storage/`

---

## 🐛 Errores Comunes

### ❌ "No autenticado"
```
401 Unauthorized
```
**Solución:** Incluye el header `Authorization: Bearer TOKEN`

### ❌ "Sin permisos"
```
403 Forbidden
```
**Solución:** Tu usuario debe tener rol `admin`

### ❌ "Campo requerido"
```
400 Bad Request
```
**Solución:** Completa todos los campos requeridos

### ❌ "La imagen debe ser un archivo válido"
```
422 Unprocessable Entity - "The image field must be a file."
```
**Solución:** Usa multipart/form-data, no JSON

### ❌ "El slug debe ser único"
```
422 - "The slug has already been taken."
```
**Solución:** Usa un slug diferente

---

## 📚 Documentación Completa

Ver: **`API_DOCUMENTATION.md`** para todos los detalles

---

## ✅ Checklist para Comenzar

- [ ] Base de datos migrada
- [ ] Usuario admin creado
- [ ] Token de autenticación obtenido
- [ ] Crear primer categoría
- [ ] Crear primer producto con imagen
- [ ] Verificar que la imagen se ve en `/storage/...`
- [ ] ¡Listo para producción! 🚀
