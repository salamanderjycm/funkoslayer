<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-75 backdrop-blur-sm">
    
    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden relative">
      
      <button 
        @click="$emit('close')" 
        class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-2xl font-bold p-2"
      >
        &times;
      </button>

      <div class="p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Finalizar Compra</h2>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
          <div class="flex justify-between items-center text-gray-600 mb-2">
            <span>Subtotal:</span>
            <span>$1.00</span>
          </div>
          <div class="flex justify-between items-center text-xl font-bold text-gray-900 border-t pt-2 mt-2">
            <span>Total:</span>
            <span>$1.10</span>
          </div>
        </div>

        <form @submit.prevent="processPayment" class="space-y-5">
          <h3 class="text-sm font-bold uppercase text-gray-400 tracking-wider">Datos Personales</h3>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
            <input 
              v-model="formData.name" 
              type="text" 
              class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-600 outline-none transition-all" 
              placeholder="Ingrese su nombre"
              required 
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input 
              v-model="formData.email" 
              type="email" 
              class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-600 outline-none transition-all" 
              placeholder="ejemplo@correo.com"
              required 
            />
          </div>

          <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg text-sm text-blue-800">
            La gestión del pago se realizará en el entorno seguro de MercadoPago.
          </div>

          <button 
            type="submit" 
            :disabled="isProcessing"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg mt-2 transition-colors disabled:opacity-50"
          >
            {{ isProcessing ? 'Procesando solicitud...' : 'Confirmar Pago' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

// Definicion de propiedades y eventos para el control de la visibilidad
defineProps({
  isOpen: { type: Boolean, required: true }
});
defineEmits(['close']);

const isProcessing = ref(false);
const formData = ref({
  name: '',
  email: '',
  phone: '000000000',
  address: 'No requerida'
});

const processPayment = async () => {
  isProcessing.value = true;
  try {
    const payload = {
      items: [{ title: 'Billy', quantity: 1, unit_price: 1.00 }],
      payer: {
        name: formData.value.name,
        email: formData.value.email,
        phone: formData.value.phone,
        address: formData.value.address
      }
    };

    const response = await api.createPaymentPreference(payload);
    if (response.success && response.init_point) {
      window.location.href = response.init_point;
    } else {
      alert('Error en la generacion de la preferencia de pago.');
    }
  } catch (error) {
    console.error('Fallo en la ejecucion del proceso:', error);
  } finally {
    isProcessing.value = false;
  }
};
</script>