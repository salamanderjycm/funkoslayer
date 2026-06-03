<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-75 z-40 transition-opacity" @click="close"></div>

  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-xl shadow-2xl max-w-sm w-full border border-gray-700 relative overflow-hidden">
      
      <button @click="close" class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl font-bold z-10">&times;</button>

      <div class="bg-gradient-to-r from-[#009EE3] to-[#0070A8] p-5">
        <h2 class="text-xl font-black text-white tracking-wide">Pagar con Mercado Pago</h2>
      </div>

      <div class="p-6">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 mb-6 flex justify-between text-white font-bold text-lg">
          <span>Total a pagar:</span>
          <span class="text-[#009EE3]">S/ {{ total.toFixed(2) }}</span>
        </div>

        <p class="text-gray-400 text-sm text-center mb-6">
          Serás redirigido a la plataforma segura de Mercado Pago para completar tu compra.
        </p>

        <button 
          @click="payWithMercadoPago" 
          :disabled="processing"
          class="w-full py-3 bg-[#009EE3] hover:bg-[#0070A8] text-white font-bold rounded-lg transition-colors flex justify-center items-center gap-2 disabled:opacity-50"
        >
          <span v-if="processing">Generando link seguro...</span>
          <span v-else>Ir a Pagar</span>
        </button>
        
        <div v-if="generalError" class="mt-4 bg-red-900/50 border border-red-700 text-red-200 px-3 py-2 rounded text-sm text-center">
          {{ generalError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { api } from '../services/api';

const props = defineProps({ isOpen: Boolean, items: Array });
const emit = defineEmits(['close']);

const processing = ref(false);
const generalError = ref('');

const total = computed(() => {
  const subtotal = props.items.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
  return subtotal * 1.10;
});

const payWithMercadoPago = async () => {
  if (total.value <= 0) return;
  
  processing.value = true;
  generalError.value = '';

  try {
    // Armamos un resumen del carrito para enviar a MP
    const orderData = {
      title: 'Compra en Funko Slayer',
      quantity: 1,
      unit_price: Number(total.value.toFixed(2))
    };

    const result = await api.createCheckoutPreference(orderData);

    if (result.success && result.init_point) {
      // Magia: Redirigimos al usuario al link oficial de MercadoPago
      window.location.href = result.init_point;
    } else {
      generalError.value = 'No se pudo conectar con la pasarela de pagos.';
      processing.value = false;
    }
  } catch (error) {
    generalError.value = 'Ocurrió un error en el servidor.';
    processing.value = false;
  }
};

const close = () => {
  generalError.value = '';
  processing.value = false;
  emit('close');
};
</script>