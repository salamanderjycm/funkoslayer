<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-70 z-40 transition-opacity" @click="close"></div>

  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-gray-700">
      
      <div class="bg-gradient-to-r from-[#FF2A85] to-[#C2185B] p-5 flex justify-between items-center sticky top-0 z-10">
        <h2 class="text-xl font-black text-white italic tracking-wide">💳 Finalizar Compra</h2>
        <button @click="close" class="text-white hover:text-gray-200 text-2xl font-bold">✕</button>
      </div>

      <div class="p-6 space-y-5">
        
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
          <h3 class="text-sm font-bold text-gray-300 mb-3 uppercase">Resumen</h3>
          <div class="flex justify-between text-white font-bold text-lg">
            <span>Total a pagar:</span>
            <span class="text-[#FF2A85]">${{ total.toFixed(2) }}</span>
          </div>
        </div>

        <form @submit.prevent="processPayment" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">Nombre Completo</label>
            <input v-model="form.fullName" type="text" placeholder="Juan García" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white focus:border-[#4DF0FF] focus:outline-none" required>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-1 uppercase">Email</label>
            <input v-model="form.email" type="email" placeholder="tu@email.com" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white focus:border-[#4DF0FF] focus:outline-none" required>
          </div>

          <div class="bg-blue-900/30 border border-blue-800 p-3 rounded text-xs text-blue-200">
            Al confirmar, será redirigido a MercadoPago para gestionar el pago de forma segura.
          </div>

          <div v-if="generalError" class="bg-red-900/50 border border-red-700 text-red-200 px-3 py-2 rounded text-sm">
            {{ generalError }}
          </div>

          <button 
            :disabled="processing"
            class="w-full bg-gradient-to-r from-[#4DF0FF] to-[#00B3CC] text-slate-900 font-black py-3 rounded-lg hover:brightness-110 transition-all uppercase shadow-lg shadow-cyan-500/20 disabled:opacity-50"
          >
            {{ processing ? 'Procesando...' : 'Confirmar Pago' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { api } from '../services/api';

const props = defineProps({ isOpen: Boolean, items: Array });
const emit = defineEmits(['close']);

const form = ref({ fullName: '', email: '' });
const processing = ref(false);
const generalError = ref('');

const total = computed(() => {
  const subtotal = props.items.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
  return subtotal * 1.10; // Incluyendo impuesto
});

const processPayment = async () => {
  processing.value = true;
  generalError.value = '';

  try {
    const payload = {
      items: props.items.map(item => ({
        title: item.name,
        quantity: item.quantity,
        unit_price: parseFloat(item.price)
      })),
      payer: {
        name: form.value.fullName,
        email: form.value.email
      }
    };

    const result = await api.createPaymentPreference(payload);

    if (result.success && result.init_point) {
     window.location.href =
  result.sandbox_init_point ??
  result.init_point;
    } else {
      generalError.value = result.error || 'Error al conectar con MercadoPago';
    }
  } catch (err) {
    generalError.value = 'Error de conexión con el servidor';
  } finally {
    processing.value = false;
  }
};

const close = () => emit('close');
</script>