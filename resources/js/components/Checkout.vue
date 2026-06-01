<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="w-full max-w-lg bg-white border border-gray-200 shadow-xl rounded-xl p-8">
      
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
            class="w-full bg-white border border-gray-300 rounded-lg p-3 text-gray-900 focus:ring-2 focus:ring-blue-600 outline-none transition-all" 
            placeholder="Ingrese su nombre"
            required 
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
          <input 
            v-model="formData.email" 
            type="email" 
            class="w-full bg-white border border-gray-300 rounded-lg p-3 text-gray-900 focus:ring-2 focus:ring-blue-600 outline-none transition-all" 
            placeholder="ejemplo@correo.com"
            required 
          />
        </div>

        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg text-sm text-blue-800">
          La gestión del pago se realizará en el entorno seguro de MercadoPago. Sus datos serán procesados mediante encriptación de nivel bancario.
        </div>

        <button 
          type="submit" 
          :disabled="isProcessing"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg mt-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isProcessing ? 'Procesando solicitud...' : 'Confirmar Pago' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

// Estado de control para el procesamiento de la transaccion
const isProcessing = ref(false);

// Estructura de datos del pagador
const formData = ref({
  name: '',
  email: '',
  phone: '000000000',
  address: 'No requerida'
});

/**
 * Gestiona el envio de datos al servidor para la creacion de la preferencia de pago
 */
const processPayment = async () => {
  isProcessing.value = true;

  try {
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
      // Redireccion automatica a la pasarela de pago oficial
      window.location.href = response.init_point;
    } else {
      alert('Se produjo un error al generar la preferencia de pago. Intente nuevamente.');
    }
  } catch (error) {
    console.error('Error durante el proceso de integracion:', error);
    alert('Fallo en la comunicacion con el servidor de pagos.');
  } finally {
    isProcessing.value = false;
  }
};
</script>