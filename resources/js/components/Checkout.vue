<template>
  <div class="checkout-container max-w-lg mx-auto bg-gray-900 p-6 rounded-lg text-white">
    <h2 class="text-2xl font-bold mb-6">Checkout</h2>

    <div class="bg-gray-800 p-4 rounded mb-6">
      <div class="flex justify-between mb-2">
        <span>Subtotal:</span>
        <span>$1.00</span>
      </div>
      <div class="flex justify-between font-bold">
        <span>Total:</span>
        <span>$1.10</span>
      </div>
    </div>

    <form @submit.prevent="processPayment" class="space-y-4">
      <h3 class="text-lg font-semibold border-b border-gray-700 pb-2">Datos Personales</h3>
      
      <div>
        <label class="block text-sm text-gray-400">Nombre Completo</label>
        <input 
          v-model="formData.name" 
          type="text" 
          class="w-full bg-gray-800 border border-gray-700 rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
          required 
        />
      </div>

      <div>
        <label class="block text-sm text-gray-400">Email</label>
        <input 
          v-model="formData.email" 
          type="email" 
          class="w-full bg-gray-800 border border-gray-700 rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
          required 
        />
      </div>

      <div class="bg-blue-900/30 border border-blue-800 p-4 rounded text-sm text-blue-200 mt-4">
        Al confirmar, sera redirigido de forma segura a MercadoPago para completar el pago. 
        Sus datos estan encriptados y protegidos.
      </div>

      <button 
        type="submit" 
        :disabled="isProcessing"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg mt-6 transition-colors disabled:opacity-50"
      >
        {{ isProcessing ? 'Procesando...' : 'Confirmar Pago' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

const isProcessing = ref(false);

const formData = ref({
  name: '',
  email: '',
  phone: '000000000', // Valor por defecto requerido por backend
  address: 'No requerida' // Valor por defecto requerido por backend
});

const processPayment = async () => {
  isProcessing.value = true;

  try {
    // Estructura de datos conforme al contrato del controlador
    const payload = {
      items: [
        { title: 'Billy', quantity: 1, unit_price: 1.00 }
      ],
      payer: {
        name: formData.value.name,
        email: formData.value.email,
        phone: formData.value.phone,
        address: formData.value.address
      }
    };

    const response = await api.createPaymentPreference(payload);

    if (response.success && response.init_point) {
      // Redireccion al flujo de MercadoPago Checkout Pro
      window.location.href = response.init_point;
    } else {
      alert('Error en la generacion de la preferencia de pago');
    }
  } catch (error) {
    console.error('Fallo en la ejecucion del proceso de pago:', error);
    alert('Ocurrio un error al conectar con la pasarela de pagos');
  } finally {
    isProcessing.value = false;
  }
};
</script>