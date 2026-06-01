<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-purple-600">📊 Panel Administrativo</h1>
          <p class="text-sm text-gray-600">Bienvenido, {{ user.name }}</p>
        </div>
        <button
          @click="handleLogout"
          class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors"
        >
          Cerrar Sesión
        </button>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex space-x-4 mb-8">
        <button
          @click="activeTab = 'list'"
          :class="activeTab === 'list' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
          class="px-6 py-2 rounded-lg font-semibold transition-colors"
        >
          📦 Productos
        </button>
        <button
          @click="activeTab = 'create'"
          :class="activeTab === 'create' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
          class="px-6 py-2 rounded-lg font-semibold transition-colors"
        >
          ➕ Nuevo Producto
        </button>
      </div>

      <div v-if="activeTab === 'list'" class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Gestionar Productos</h2>

        <div v-if="loading" class="text-center text-gray-500">
          <p>Cargando productos...</p>
        </div>

        <div v-else-if="products.length === 0" class="text-center text-gray-500 py-8">
          <p>No hay productos aún</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nombre</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Categoría</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Precio</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Stock</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id" class="border-b hover:bg-gray-50">
                <td class="px-4 py-3">{{ product.name }}</td>
                <td class="px-4 py-3">{{ product.category?.name }}</td>
                <td class="px-4 py-3">${{ parseFloat(product.price).toFixed(2) }}</td>
                <td class="px-4 py-3">
                  <span :class="product.stock > 10 ? 'text-green-600' : product.stock > 0 ? 'text-yellow-600' : 'text-red-600'" class="font-semibold">
                    {{ product.stock }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <button
                    @click="editProduct(product)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg mr-2 transition-colors text-sm"
                  >
                    ✏️ Editar
                  </button>
                  <button
                    @click="deleteProduct(product.id)"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition-colors text-sm"
                  >
                    🗑️ Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeTab === 'create'" class="bg-white rounded-lg shadow-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold mb-6">{{ editingProduct ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h2>

        <form @submit.prevent="handleSaveProduct" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
            <input
              v-model="formData.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
            <input
              v-model="formData.slug"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
            <textarea
              v-model="formData.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
              rows="3"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Precio *</label>
              <input
                v-model="formData.price"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Costo</label>
              <input
                v-model="formData.cost"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Stock *</label>
              <input
                v-model="formData.stock"
                type="number"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
              <select
                v-model="formData.category_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                required
              >
                <option value="">Seleccionar categoría</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Imagen del Producto</label>
            <input
              type="file"
              @change="handleImageUpload"
              accept="image/*"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
            />
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="formData.active"
                type="checkbox"
                class="w-4 h-4 text-purple-600 rounded focus:ring-2 focus:ring-purple-600"
              />
              <span class="ml-2 text-sm text-gray-700">Producto activo</span>
            </label>
          </div>

          <div v-if="formError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ formError }}
          </div>

          <div class="flex space-x-4 pt-4">
            <button
              type="submit"
              :disabled="savingProduct"
              class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ savingProduct ? 'Guardando...' : editingProduct ? 'Actualizar' : 'Crear' }}
            </button>
            <button
              type="button"
              @click="cancelEdit"
              class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition-colors"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../services/api.js';

const props = defineProps({
  user: { type: Object, required: true },
});

const emit = defineEmits(['logout']);

const activeTab = ref('list');
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const savingProduct = ref(false);
const formError = ref('');
const editingProduct = ref(null);

const formData = ref({
  name: '',
  slug: '',
  description: '',
  price: '',
  cost: '',
  stock: '',
  category_id: '',
  image: '',
  active: true,
});

// ✅ NUEVA FUNCIÓN: Atrapa el archivo de imagen seleccionado
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    formData.value.image = file;
  }
};

const loadProducts = async () => {
  loading.value = true;
  products.value = await api.getAdminProducts();
  loading.value = false;
};

const loadCategories = async () => {
  categories.value = await api.getCategories();
};

const editProduct = (product) => {
  editingProduct.value = product;
  formData.value = {
    name: product.name,
    slug: product.slug,
    description: product.description,
    price: product.price,
    cost: product.cost,
    stock: product.stock,
    category_id: product.category_id,
    image: product.image,
    active: product.active,
  };
  activeTab.value = 'create';
};

const cancelEdit = () => {
  editingProduct.value = null;
  formData.value = {
    name: '',
    slug: '',
    description: '',
    price: '',
    cost: '',
    stock: '',
    category_id: '',
    image: '',
    active: true,
  };
  
  // Limpiamos el input de tipo file visualmente
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = '';
};

const handleSaveProduct = async () => {
  formError.value = '';
  savingProduct.value = true;

  const data = {
    ...formData.value,
    price: parseFloat(formData.value.price),
    cost: formData.value.cost ? parseFloat(formData.value.cost) : null,
    stock: parseInt(formData.value.stock),
  };

  let result;
  if (editingProduct.value) {
    result = await api.updateProduct(editingProduct.value.id, data);
  } else {
    result = await api.createProduct(data);
  }

  if (result.success) {
    await loadProducts();
    cancelEdit();
    activeTab.value = 'list';
  } else {
    formError.value = result.message || 'Error guardando producto';
  }

  savingProduct.value = false;
};

const deleteProduct = async (id) => {
  if (confirm('¿Estás seguro de eliminar este producto?')) {
    const result = await api.deleteProduct(id);
    if (result.success) {
      await loadProducts();
    } else {
      alert('Error eliminando producto: ' + result.message);
    }
  }
};

const handleLogout = async () => {
  await api.logout();
  emit('logout');
};

onMounted(() => {
  loadProducts();
  loadCategories();
});
</script>

<style scoped>
/* Admin panel styles */
</style>