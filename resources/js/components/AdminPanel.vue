<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-purple-600">Panel Administrativo</h1>
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
      <div class="flex flex-wrap gap-4 mb-8">
        <button
          @click="activeTab = 'orders'"
          :class="activeTab === 'orders' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-700 border hover:bg-gray-50'"
          class="px-6 py-2 rounded-lg font-semibold transition-all"
        >
          📦 Gestionar Pedidos
        </button>
        <button
          @click="activeTab = 'list'"
          :class="activeTab === 'list' ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-gray-700 border hover:bg-gray-50'"
          class="px-6 py-2 rounded-lg font-semibold transition-all"
        >
          Gestionar Productos
        </button>
        <button
          @click="activeTab = 'create'"
          :class="activeTab === 'create' ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-gray-700 border hover:bg-gray-50'"
          class="px-6 py-2 rounded-lg font-semibold transition-all"
        >
          Nuevo Producto
        </button>
        <button
          @click="activeTab = 'categories'"
          :class="activeTab === 'categories' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 border hover:bg-gray-50'"
          class="px-6 py-2 rounded-lg font-semibold transition-all"
        >
          Gestionar Categorías
        </button>
      </div>

      <div v-if="activeTab === 'orders'" class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Control de Ventas</h2>
          <button @click="fetchOrders" class="text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-2 text-sm font-bold">
            <span>🔄</span> Refrescar Lista
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div class="bg-gray-50 border rounded-lg p-4">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total de Ventas</h3>
            <p class="text-2xl font-black text-emerald-600">S/ {{ totalIngresos.toFixed(2) }}</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Pedidos Realizados</h3>
            <p class="text-2xl font-black text-purple-600">{{ orders.length }}</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Pendientes de Envío</h3>
            <p class="text-2xl font-black text-amber-500">{{ pedidosPendientes }}</p>
          </div>
        </div>

        <div v-if="loadingOrders" class="text-center text-gray-500 py-8">
          <p>Cargando registros de ventas...</p>
        </div>

        <div v-else-if="orders.length === 0" class="text-center text-gray-500 py-8 border rounded-lg bg-gray-50">
          <p>No hay pedidos registrados en el sistema todavía.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-sm font-semibold text-gray-700">Orden / Fecha</th>
                <th class="px-4 py-3 text-sm font-semibold text-gray-700">Cliente</th>
                <th class="px-4 py-3 text-sm font-semibold text-gray-700">Artículos</th>
                <th class="px-4 py-3 text-sm font-semibold text-gray-700">Total</th>
                <th class="px-4 py-3 text-sm font-semibold text-gray-700">Estado de Operación</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <span class="font-bold text-gray-800">#{{ order.id }}</span>
                  <p class="text-xs text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
                </td>
                <td class="px-4 py-3">
                  <div v-if="order.user">
                    <p class="font-bold text-gray-800 text-sm">{{ order.user.name }}</p>
                    <p class="text-xs text-gray-500">{{ order.user.email }}</p>
                  </div>
                  <span v-else class="text-gray-400 italic text-sm">Usuario Eliminado</span>
                </td>
                <td class="px-4 py-3">
                  <ul class="text-xs space-y-1">
                    <li v-for="(item, i) in order.items" :key="i" class="text-gray-600">
                      <span class="font-bold text-purple-600">{{ item.quantity }}x</span> {{ item.title }}
                    </li>
                  </ul>
                </td>
                <td class="px-4 py-3 font-bold text-gray-800">
                  S/ {{ parseFloat(order.total).toFixed(2) }}
                </td>
                <td class="px-4 py-3">
                  <select 
                    v-model="order.status" 
                    @change="changeOrderStatus(order.id, order.status)"
                    class="text-sm font-semibold border rounded-lg px-3 py-1 cursor-pointer outline-none"
                    :class="getStatusSelectClass(order.status)"
                  >
                    <option value="pendiente" class="text-amber-600">Pendiente</option>
                    <option value="aprobado" class="text-emerald-600">Aprobado</option>
                    <option value="enviado" class="text-blue-600">Enviado</option>
                    <option value="entregado" class="text-purple-600">Entregado</option>
                    <option value="rechazado" class="text-red-600">Rechazado</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeTab === 'list'" class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Gestionar Productos</h2>
        <div v-if="loading" class="text-center text-gray-500">
          <p>Cargando productos...</p>
        </div>
        <div v-else-if="products.length === 0" class="text-center text-gray-500 py-8">
          <p>No se encontraron productos registrados</p>
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
                <td class="px-4 py-3">{{ product.category?.name || 'Sin Categoría' }}</td>
                <td class="px-4 py-3">S/ {{ parseFloat(product.price).toFixed(2) }}</td>
                <td class="px-4 py-3">
                  <span :class="product.stock > 10 ? 'text-green-600' : product.stock > 0 ? 'text-yellow-600' : 'text-red-600'" class="font-semibold">
                    {{ product.stock }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <button @click="editProduct(product)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg mr-2 transition-colors text-sm">
                    Editar
                  </button>
                  <button @click="deleteProduct(product.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition-colors text-sm">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeTab === 'create'" class="bg-white rounded-lg shadow-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold mb-6">{{ editingProduct ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h2>
        <div v-if="categories.length === 0" class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded text-yellow-800 text-sm">
          Atención: No existen categorías registradas en el sistema. Se recomienda crear una categoría desde la pestaña correspondiente antes de proceder con el registro de productos.
        </div>
        <form @submit.prevent="handleSaveProduct" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
              <input v-model="formData.name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
              <input v-model="formData.slug" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" required />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
            <textarea v-model="formData.description" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" rows="3"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Precio de Venta * (S/)</label>
              <input v-model="formData.price" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Costo Adquisición (S/)</label>
              <input v-model="formData.cost" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Inventario disponible (Stock) *</label>
              <input v-model="formData.stock" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Asignación de Categoría *</label>
              <select v-model="formData.category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" required>
                <option value="">Seleccionar categoría</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recurso de Imagen del Producto</label>
            <input type="file" @change="handleImageUpload" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-1 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" />
          </div>
          <div>
            <label class="flex items-center">
              <input v-model="formData.active" type="checkbox" class="w-4 h-4 text-purple-600 rounded focus:ring-2 focus:ring-purple-600" />
              <span class="ml-2 text-sm text-gray-700">Habilitar estado activo para visualización</span>
            </label>
          </div>
          <div v-if="formError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">{{ formError }}</div>
          <div class="flex space-x-4 pt-4 border-t">
            <button type="submit" :disabled="savingProduct" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition-colors disabled:opacity-50">
              {{ savingProduct ? 'Procesando...' : editingProduct ? 'Actualizar Registro' : 'Registrar Producto' }}
            </button>
            <button type="button" @click="cancelEdit" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition-colors">
              Cancelar
            </button>
          </div>
        </form>
      </div>

      <div v-if="activeTab === 'categories'" class="bg-white rounded-lg shadow-lg p-6 max-w-4xl">
        <h2 class="text-2xl font-bold mb-6">Gestión de Categorías</h2>
        <form @submit.prevent="handleSaveCategory" class="mb-8 space-y-4 bg-blue-50/50 p-6 rounded-lg border border-blue-100">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Categoría *</label>
                <input v-model="catForm.name" @input="generateCatSlug" type="text" placeholder="Ej. Series Animadas" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Identificador de URL (Slug) *</label>
                <input v-model="catForm.slug" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed" readonly />
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Imagen Alusiva (Opcional, max 2MB)</label>
                <input type="file" ref="catFileInput" @change="handleCatImageUpload" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" class="w-full px-4 py-1.5 border border-gray-300 bg-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción General</label>
                <textarea v-model="catForm.description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
              </div>
            </div>
          </div>
          <div class="flex justify-end pt-4 border-t border-blue-100 mt-4">
            <button type="submit" :disabled="savingCategory" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-medium transition-colors disabled:opacity-50">
              {{ savingCategory ? 'Procesando...' : 'Registrar Categoría' }}
            </button>
          </div>
          <p v-if="catSuccessMsg" class="text-sm mt-2 text-green-700 bg-green-100 p-3 rounded-lg">{{ catSuccessMsg }}</p>
          <p v-if="catErrorMsg" class="text-sm mt-2 text-red-700 bg-red-100 p-3 rounded-lg">{{ catErrorMsg }}</p>
        </form>
        <div>
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Categorías Vigentes ({{ categories.length }})</h3>
          <div v-if="categories.length === 0" class="text-gray-500 text-sm italic p-8 text-center border rounded-lg bg-gray-50">
            No se han detectado categorías registradas en el sistema.
          </div>
          <ul v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <li v-for="category in categories" :key="category.id" class="flex items-center p-4 border rounded-lg bg-white shadow-sm">
              <div class="w-14 h-14 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center border mr-4">
                <img v-if="category.image" :src="`/storage/${category.image}`" class="w-full h-full object-cover" />
                <span v-else class="text-gray-400 text-xs text-center">Sin recurso<br>multimedia</span>
              </div>
              <div class="flex-1 overflow-hidden">
                <h4 class="font-bold text-gray-800 truncate">{{ category.name }}</h4>
                <p class="text-xs text-blue-600 font-mono mt-0.5 truncate">/{{ category.slug }}</p>
                <p v-if="category.description" class="text-sm text-gray-500 mt-1 truncate">{{ category.description }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { api } from '../services/api.js';

const props = defineProps({
  user: { type: Object, required: true },
});

const emit = defineEmits(['logout']);

// Declaración de estados lógicos globales
const activeTab = ref('orders'); // Cambiado para que inicie en la pestaña de Pedidos
const loading = ref(false);

// ==========================================
// ESTADO Y LÓGICA DE PEDIDOS (NUEVO)
// ==========================================
const orders = ref([]);
const loadingOrders = ref(true);

const fetchOrders = async () => {
  loadingOrders.value = true;
  const result = await api.getAllOrders();
  if (result.success) {
    orders.value = result.orders;
  }
  loadingOrders.value = false;
};

const changeOrderStatus = async (orderId, newStatus) => {
  const result = await api.updateOrderStatus(orderId, newStatus);
  if (result.success) {
    alert(`Orden #${orderId} actualizada a: ${newStatus.toUpperCase()}`);
  } else {
    alert('Error al actualizar el estado de la orden');
  }
};

const totalIngresos = computed(() => {
  return orders.value
    .filter(o => ['aprobado', 'enviado', 'entregado'].includes(o.status.toLowerCase()))
    .reduce((sum, o) => sum + parseFloat(o.total), 0);
});

const pedidosPendientes = computed(() => {
  return orders.value.filter(o => o.status.toLowerCase() === 'aprobado').length;
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-PE', { 
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
  });
};

const getStatusSelectClass = (status) => {
  const s = status.toLowerCase();
  if (s === 'aprobado') return 'bg-emerald-50 text-emerald-700 border-emerald-300';
  if (s === 'enviado' || s === 'entregado') return 'bg-blue-50 text-blue-700 border-blue-300';
  if (s === 'pendiente') return 'bg-amber-50 text-amber-700 border-amber-300';
  if (s === 'rechazado') return 'bg-red-50 text-red-700 border-red-300';
  return 'bg-gray-50 text-gray-700 border-gray-300';
};


// ==========================================
// ESTADO Y LÓGICA DE PRODUCTOS
// ==========================================
const products = ref([]);
const savingProduct = ref(false);
const formError = ref('');
const editingProduct = ref(null);

const formData = ref({
  name: '', slug: '', description: '', price: '', cost: '', stock: '', category_id: '', image: '', active: true,
});

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) formData.value.image = file;
};

const loadProducts = async () => {
  loading.value = true;
  products.value = await api.getAdminProducts();
  loading.value = false;
};

const editProduct = (product) => {
  editingProduct.value = product;
  formData.value = {
    name: product.name, slug: product.slug, description: product.description, price: product.price, cost: product.cost, stock: product.stock, category_id: product.category_id, image: '', active: product.active,
  };
  activeTab.value = 'create';
};

const cancelEdit = () => {
  editingProduct.value = null;
  formData.value = { name: '', slug: '', description: '', price: '', cost: '', stock: '', category_id: '', image: '', active: true };
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = '';
  activeTab.value = 'list';
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
  } else {
    formError.value = result.message || 'Error durante la persistencia del producto';
  }
  savingProduct.value = false;
};

const deleteProduct = async (id) => {
  if (confirm('¿Confirma la eliminación permanente de este registro de producto?')) {
    const result = await api.deleteProduct(id);
    if (result.success) {
      await loadProducts();
    } else {
      alert('Error en la baja del producto: ' + result.message);
    }
  }
};


// ==========================================
// ESTADO Y LÓGICA DE CATEGORÍAS
// ==========================================
const categories = ref([]);
const savingCategory = ref(false);
const catSuccessMsg = ref('');
const catErrorMsg = ref('');
const catFileInput = ref(null);

const catForm = ref({ name: '', slug: '', description: '', image: null });

const generateCatSlug = () => {
  catForm.value.slug = catForm.value.name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
};

const handleCatImageUpload = (event) => {
  catForm.value.image = event.target.files[0];
};

const loadCategories = async () => {
  try {
    const currentToken = localStorage.getItem('auth_token') || localStorage.getItem('token');
    const response = await fetch('/api/categories', { headers: { 'Authorization': `Bearer ${currentToken}` } });
    const result = await response.json();
    if (response.ok && result.success) {
      categories.value = result.data;
    } else {
      categories.value = Array.isArray(result) ? result : [];
    }
  } catch (err) {
    console.error('Fallo crítico al recuperar la colección de categorías:', err);
  }
};

const handleSaveCategory = async () => {
  catSuccessMsg.value = '';
  catErrorMsg.value = '';
  savingCategory.value = true;
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
  const fd = new FormData();
  fd.append('name', catForm.value.name);
  fd.append('slug', catForm.value.slug);
  if (catForm.value.description) fd.append('description', catForm.value.description);
  if (catForm.value.image) fd.append('image', catForm.value.image);

  try {
    const response = await fetch('/api/admin/categories', {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
      body: fd
    });
    const result = await response.json();
    if (response.ok && result.success) {
      catSuccessMsg.value = 'Categoría creada con éxito';
      await loadCategories();
      catForm.value = { name: '', slug: '', description: '', image: null };
    } else {
      catErrorMsg.value = result.message || 'Error de autorización';
    }
  } catch (err) {
    catErrorMsg.value = 'Error de conexión con el servidor';
  } finally {
    savingCategory.value = false;
  }
};

const handleLogout = async () => {
  await api.logout();
  emit('logout');
};

// ==========================================
// CICLO DE VIDA
// ==========================================
onMounted(() => {
  loadCategories();
  loadProducts();
  fetchOrders(); // Iniciamos la carga de ventas automáticamente
});
</script>