<template>
  <div class="p-6 bg-white rounded-lg shadow-md max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Gestión de Categorías</h2>

    <form @submit.prevent="createCategory" class="mb-8 space-y-4 bg-gray-50 p-6 rounded-lg border">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Categoría *</label>
            <input 
              v-model="form.name" 
              @input="generateSlug"
              type="text" 
              placeholder="Ej. Funkos Marvel" 
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">URL amigable (Slug) *</label>
            <input 
              v-model="form.slug" 
              type="text" 
              class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
              readonly
            />
          </div>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Imagen (Opcional, max 2MB)</label>
            <input 
              type="file" 
              ref="fileInput"
              @change="handleImageUpload" 
              accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
              class="w-full px-4 py-1.5 border rounded-lg bg-white text-sm text-gray-600 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea 
              v-model="form.description" 
              rows="2" 
              placeholder="Descripción de la categoría..."
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="flex justify-end pt-4 border-t mt-4">
        <button 
          type="submit" 
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting" class="animate-spin mr-2">⏳</span>
          {{ isSubmitting ? 'Guardando...' : 'Guardar Categoría' }}
        </button>
      </div>

      <p v-if="message" class="text-sm mt-2 text-green-600 font-medium bg-green-50 p-2 rounded">{{ message }}</p>
      <p v-if="error" class="text-sm mt-2 text-red-600 font-medium bg-red-50 p-2 rounded">{{ error }}</p>
    </form>

    <div>
      <h3 class="text-lg font-semibold text-gray-700 mb-4">Categorías Registradas ({{ categories.length }})</h3>
      <div v-if="categories.length === 0" class="text-gray-500 text-sm italic p-8 text-center border rounded-lg bg-gray-50">
        No hay categorías creadas todavía.
      </div>
      <ul v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <li 
          v-for="category in categories" 
          :key="category.id" 
          class="flex items-center p-4 border rounded-lg hover:shadow-sm transition-shadow bg-white"
        >
          <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center border mr-4">
            <img v-if="category.image" :src="`/storage/${category.image}`" class="w-full h-full object-cover" />
            <span v-else class="text-gray-400 text-xs text-center">Sin<br>foto</span>
          </div>
          
          <div class="flex-1 overflow-hidden">
            <h4 class="font-bold text-gray-800 truncate">{{ category.name }}</h4>
            <p class="text-xs text-blue-600 font-mono mt-1 truncate">/{{ category.slug }}</p>
            <p v-if="category.description" class="text-sm text-gray-500 mt-1 truncate">{{ category.description }}</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const categories = ref([]);
const form = ref({ name: '', slug: '', description: '' });
const imageFile = ref(null);
const fileInput = ref(null);
const message = ref('');
const error = ref('');
const isSubmitting = ref(false);

const token = localStorage.getItem('auth_token');

// Generador de slug en tiempo real
const generateSlug = () => {
  form.value.slug = form.value.name
    .toLowerCase()
    .normalize('NFD') // Quita acentos
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '') // Quita caracteres especiales
    .trim()
    .replace(/\s+/g, '-'); // Cambia espacios por guiones
};

const handleImageUpload = (event) => {
  imageFile.value = event.target.files[0];
};

const fetchCategories = async () => {
  try {
    const response = await fetch('/api/categories', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    const result = await response.json();
    if (response.ok && result.success) {
      // ACCEDEMOS AL ARRAY 'data' QUE RETORNA TU CONTROLADOR
      categories.value = result.data;
    }
  } catch (err) {
    console.error('Error al cargar categorías:', err);
  }
};

const createCategory = async () => {
  message.value = '';
  error.value = '';
  isSubmitting.value = true;
  
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('slug', form.value.slug); // Enviamos el slug generado
  if (form.value.description) formData.append('description', form.value.description);
  if (imageFile.value) formData.append('image', imageFile.value);

  try {
    const response = await fetch('/api/categories', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: formData
    });

    const result = await response.json();

    if (response.ok && result.success) {
      message.value = result.message;
      
      // Limpiar formulario completo
      form.value = { name: '', slug: '', description: '' };
      imageFile.value = null;
      if (fileInput.value) fileInput.value.value = ''; 
      
      fetchCategories();
      
      // Quitar el mensaje de éxito después de 3 segundos
      setTimeout(() => { message.value = ''; }, 3000);
    } else {
      // Manejar errores de validación de Laravel (ej: nombre o slug duplicado)
      if (result.errors) {
        error.value = Object.values(result.errors).flat().join(' | ');
      } else {
        error.value = result.message || 'Error al crear la categoría';
      }
    }
  } catch (err) {
    error.value = 'Ocurrió un error de conexión con el servidor.';
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchCategories();
});
</script>