<template>
  <!-- Overlay -->
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity duration-300" @click="close"></div>

  <!-- Modal Checkout -->
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-[#FF2A85] to-[#C2185B] p-6 flex justify-between items-center sticky top-0">
        <h2 class="text-2xl font-black text-white italic tracking-wide">💳 Checkout</h2>
        <button 
          @click="close"
          class="text-white hover:text-gray-200 transition-colors text-2xl font-bold"
        >
          ✕
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        
        <!-- Resumen del Pedido -->
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
          <h3 class="text-lg font-bold text-white mb-4">📦 Resumen del Pedido</h3>
          <div class="space-y-2 max-h-48 overflow-y-auto">
            <div v-for="item in items" :key="item.id" class="flex justify-between items-center text-sm bg-gray-900 p-2 rounded">
              <div>
                <span class="text-white font-semibold">{{ item.name }}</span>
                <span class="text-gray-400 text-xs ml-2">x{{ item.quantity }}</span>
              </div>
              <span class="text-[#4DF0FF] font-bold">${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          
          <div class="border-t border-gray-700 mt-3 pt-3 space-y-1">
            <div class="flex justify-between text-gray-400 text-sm">
              <span>Subtotal:</span>
              <span>${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-gray-400 text-sm">
              <span>Impuesto (10%):</span>
              <span>${{ tax.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-white font-black text-lg mt-2 pt-2 border-t border-gray-700">
              <span>Total:</span>
              <span class="text-[#FF2A85]">${{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- Datos Personales -->
        <div>
          <h3 class="text-lg font-bold text-white mb-3">👤 Datos Personales</h3>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-semibold text-gray-300 mb-1">Nombre Completo</label>
              <input 
                v-model="form.fullName" 
                type="text" 
                placeholder="Juan García" 
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
              >
              <span v-if="errors.fullName" class="text-red-500 text-xs mt-1">{{ errors.fullName }}</span>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Email</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  placeholder="tu@email.com" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
                >
                <span v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</span>
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Teléfono</label>
                <input 
                  v-model="form.phone" 
                  type="tel" 
                  placeholder="+34 123 456 789" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
                >
                <span v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-300 mb-1">Dirección</label>
              <input 
                v-model="form.address" 
                type="text" 
                placeholder="Calle Principal 123" 
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
              >
              <span v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</span>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Ciudad</label>
                <input 
                  v-model="form.city" 
                  type="text" 
                  placeholder="Madrid" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
                >
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Estado/Provincia</label>
                <input 
                  v-model="form.state" 
                  type="text" 
                  placeholder="Madrid" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
                >
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Código Postal</label>
                <input 
                  v-model="form.zipCode" 
                  type="text" 
                  placeholder="28001" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Datos de Tarjeta -->
        <div>
          <h3 class="text-lg font-bold text-white mb-3">💳 Datos de Pago</h3>
          <div class="bg-blue-900 border border-blue-700 rounded-lg p-4 mb-3">
            <p class="text-blue-200 text-sm">
              Serás redirigido de forma segura a <strong>MercadoPago</strong> para completar el pago con tu tarjeta.
              Todos los datos están encriptados y seguros.
            </p>
          </div>
          
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-semibold text-gray-300 mb-1">Número de Tarjeta</label>
              <input 
                v-model="form.cardNumber" 
                type="text" 
                placeholder="1234 5678 9012 3456" 
                maxlength="19"
                @input="formatCardNumber"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors font-mono"
              >
              <span v-if="errors.cardNumber" class="text-red-500 text-xs mt-1">{{ errors.cardNumber }}</span>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-300 mb-1">Nombre en la Tarjeta</label>
                <input 
                  v-model="form.cardName" 
                  type="text" 
                  placeholder="JUAN GARCIA" 
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors uppercase"
                >
                <span v-if="errors.cardName" class="text-red-500 text-xs mt-1">{{ errors.cardName }}</span>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">CVV</label>
                <input 
                  v-model="form.cvv" 
                  type="text" 
                  placeholder="123" 
                  maxlength="4"
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors font-mono"
                >
                <span v-if="errors.cvv" class="text-red-500 text-xs mt-1">{{ errors.cvv }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-semibold text-gray-300 mb-1">Vencimiento (MM/AA)</label>
                <input 
                  v-model="form.expiryDate" 
                  type="text" 
                  placeholder="12/25" 
                  maxlength="5"
                  @input="formatExpiryDate"
                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded text-white placeholder-gray-500 focus:border-[#4DF0FF] focus:outline-none transition-colors font-mono"
                >
                <span v-if="errors.expiryDate" class="text-red-500 text-xs mt-1">{{ errors.expiryDate }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Error General -->
        <div v-if="generalError" class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded">
          {{ generalError }}
        </div>

        <!-- Botones -->
        <div class="flex gap-3 pt-4 border-t border-gray-700">
          <button
            @click="close"
            class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 uppercase"
          >
            Cancelar
          </button>
          
          <button
            @click="processPayment"
            :disabled="processing"
            class="flex-1 bg-gradient-to-r from-[#4DF0FF] to-[#00B3CC] hover:from-[#00B3CC] hover:to-[#0099AA] disabled:from-gray-600 disabled:to-gray-600 text-slate-900 font-black py-2 px-4 rounded-lg transition-all duration-200 uppercase shadow-lg shadow-cyan-500/40 hover:scale-105 transform disabled:scale-100"
          >
            {{ processing ? 'Procesando...' : '✓ Confirmar Pago' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api } from '../services/api';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  items: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['close', 'payment-success']);

let mercadopago = null;

// Form data
const form = ref({
  fullName: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zipCode: '',
  cardNumber: '',
  cardName: '',
  cvv: '',
  expiryDate: ''
});

// Validation errors
const errors = ref({});
const generalError = ref('');
const processing = ref(false);

// Computed
const subtotal = computed(() => {
  return props.items.reduce((total, item) => {
    return total + (parseFloat(item.price) * item.quantity);
  }, 0);
});

const tax = computed(() => {
  return subtotal.value * 0.1; // 10% tax
});

const total = computed(() => {
  return subtotal.value + tax.value;
});

// Methods
const formatCardNumber = () => {
  let value = form.value.cardNumber.replace(/\s+/g, '');
  let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
  form.value.cardNumber = formatted;
};

const formatExpiryDate = () => {
  let value = form.value.expiryDate.replace(/\D/g, '');
  if (value.length >= 2) {
    value = value.substring(0, 2) + '/' + value.substring(2, 4);
  }
  form.value.expiryDate = value;
};

const validateForm = () => {
  errors.value = {};
  generalError.value = '';

  // Personal data validation only (card handled by MercadoPago)
  if (!form.value.fullName.trim()) {
    errors.value.fullName = 'Nombre requerido';
  }
  if (!form.value.email.trim()) {
    errors.value.email = 'Email requerido';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Email inválido';
  }
  if (!form.value.phone.trim()) {
    errors.value.phone = 'Teléfono requerido';
  }
  if (!form.value.address.trim()) {
    errors.value.address = 'Dirección requerida';
  }

  return Object.keys(errors.value).length === 0;
};

// Initialize MercadoPago
onMounted(async () => {
  // No need to load SDK - using server-side preferences
});

const processPayment = async () => {
  if (!validateForm()) {
    generalError.value = 'Por favor, completa todos los campos correctamente';
    return;
  }

  processing.value = true;

  try {
    // Preparar datos para crear preferencia de pago
    const preferenceData = {
      items: props.items.map(item => ({
        title: item.name,
        quantity: item.quantity,
        unit_price: parseFloat(item.price)
      })),
      payer: {
        name: form.value.fullName,
        email: form.value.email,
        phone: form.value.phone.replace(/\D/g, ''),
        address: form.value.address
      },
      external_reference: `order_${Date.now()}`,
    };

    // Crear preferencia en el backend
    const preferenceResult = await api.createPaymentPreference(preferenceData);

    if (preferenceResult.error) {
      generalError.value = preferenceResult.error || 'Error creando preferencia de pago';
      processing.value = false;
      return;
    }

    // Redirigir a MercadoPago Checkout
    if (preferenceResult.init_point) {
      // Para sandbox
      window.location.href = preferenceResult.sandbox_init_point || preferenceResult.init_point;
    } else if (preferenceResult.preference_id) {
      // Alternativa: abrir en nueva ventana
      const checkoutUrl = `https://www.mercadopago.com/checkout/v1/redirect?preference-id=${preferenceResult.preference_id}`;
      window.location.href = checkoutUrl;
    } else {
      generalError.value = 'No se pudo obtener el enlace de pago';
      processing.value = false;
    }
  } catch (error) {
    console.error('Payment error:', error);
    generalError.value = 'Error al procesar el pago. Intenta de nuevo.';
    processing.value = false;
  }
};

const close = () => {
  emit('close');
};
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #1f2937;
}

::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>
