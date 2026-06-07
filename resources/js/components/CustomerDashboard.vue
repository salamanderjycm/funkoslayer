<template>
    <div class="min-h-screen bg-gray-950 text-white p-6 relative overflow-hidden">
    <!-- Efectos de neón de fondo -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#FF2A85] rounded-full filter blur-[150px] opacity-10"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-cyan-500 rounded-full filter blur-[150px] opacity-10"></div>

    <div class="max-w-4xl mx-auto relative z-10 pt-10">
        <h1 class="text-4xl font-black tracking-wider italic mb-2">
        MIS <span class="text-cyan-400">PEDIDOS</span>
        </h1>
        <p class="text-gray-400 text-sm mb-10">Revisa el historial de tus cacerías en Funko Slayer.</p>

        <!-- Pantalla de carga -->
        <div v-if="loading" class="text-center py-20">
        <div class="w-12 h-12 border-4 border-gray-800 border-t-[#FF2A85] rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-gray-500 tracking-widest text-sm uppercase">Cargando arsenal...</p>
        </div>

        <!-- Pantalla vacía (Sin compras) -->
        <div v-else-if="orders.length === 0" class="bg-gray-900/60 backdrop-blur-md border border-gray-800 rounded-2xl p-12 text-center shadow-2xl">
        <span class="text-6xl mb-4 block">🛒</span>
        <h3 class="text-xl font-bold mb-2">Aún no tienes pedidos</h3>
        <p class="text-gray-400 text-sm mb-6">Tu colección de Funkos está vacía. ¡Es hora de cazar!</p>
        <button @click="$router.push('/')" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-bold uppercase tracking-wide shadow-lg shadow-cyan-500/20 hover:opacity-90 transition-all">
            Ir a la Tienda
            </button>
        </div>

        <!-- Lista de Órdenes -->
        <div v-else class="space-y-6">
        <div v-for="order in orders" :key="order.id" class="bg-gray-900/80 backdrop-blur-xl border border-gray-800 rounded-2xl overflow-hidden shadow-2xl transition-all hover:border-gray-700">
            
            <!-- Cabecera de la Orden -->
            <div class="bg-gray-950/50 p-5 flex flex-wrap justify-between items-center border-b border-gray-800">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Orden #{{ order.id }}</p>
                <p class="text-sm font-medium text-gray-300 mt-1">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="flex items-center gap-4 mt-3 sm:mt-0">
                <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">
                {{ order.status }}
                </span>
                <p class="text-xl font-black text-[#FF2A85]">S/ {{ order.total }}</p>
            </div>
            </div>

            <!-- Detalles de los Productos comprados -->
            <div class="p-5">
            <h4 class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-4">Artículos adquiridos</h4>
            <ul class="space-y-3">
                <li v-for="(item, index) in order.items" :key="index" class="flex justify-between items-center bg-gray-950/30 rounded-lg p-3 border border-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-800 rounded flex items-center justify-center text-xl">
                    📦
                    </div>
                    <div>
                    <p class="font-medium text-sm text-gray-200">{{ item.title }}</p>
                    <p class="text-xs text-gray-500">Cantidad: {{ item.quantity }}</p>
                    </div>
                </div>
                <p class="text-sm font-bold text-gray-300">S/ {{ (item.price * item.quantity).toFixed(2) }}</p>
                </li>
            </ul>
            </div>

        </div>
        </div>

    </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../services/api.js'; // Ajusta la ruta a tu archivo api.js

const orders = ref([]);
const loading = ref(true);

onMounted(async () => {
  await fetchOrders();
});

const fetchOrders = async () => {
  loading.value = true;
  const result = await api.getMyOrders();
  if (result.success) {
    orders.value = result.orders;
  }
  loading.value = false;
};

// Utilidad para formatear la fecha
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('es-PE', options);
};

// Utilidad para pintar el estado de la orden de un color diferente
const getStatusClass = (status) => {
  const s = status.toLowerCase();
  if (s === 'aprobado' || s === 'approved') return 'bg-emerald-900/50 text-emerald-400 border border-emerald-800';
  if (s === 'pendiente' || s === 'pending') return 'bg-amber-900/50 text-amber-400 border border-amber-800';
  if (s === 'rechazado' || s === 'rejected' || s === 'failure') return 'bg-red-900/50 text-red-400 border border-red-800';
  return 'bg-gray-800 text-gray-300 border border-gray-700';
};
</script>