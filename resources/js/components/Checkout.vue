<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-75 z-40 transition-opacity" @click="close"></div>

  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-gray-700 relative">
      
      <button @click="close" class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl font-bold z-10">&times;</button>

      <div class="bg-gradient-to-r from-[#FF2A85] to-[#C2185B] p-5 sticky top-0 z-0">
        <h2 class="text-xl font-black text-white italic tracking-wide">💳 Pago Seguro</h2>
      </div>

      <div class="p-6">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 mb-6 flex justify-between text-white font-bold text-lg">
          <span>Total a pagar:</span>
          <span class="text-[#FF2A85]">S/ {{ total.toFixed(2) }}</span>
        </div>

        <div id="paymentBrick_container" class="min-h-[300px]"></div>

        <div v-if="processing" class="text-center text-cyan-400 mt-4 font-bold animate-pulse">
          Procesando transacción, por favor espera...
        </div>
        
        <div v-if="generalError" class="mt-4 bg-red-900/50 border border-red-700 text-red-200 px-3 py-2 rounded text-sm text-center">
          {{ generalError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { api } from '../services/api';

const props = defineProps({ isOpen: Boolean, items: Array });
const emit = defineEmits(['close', 'payment-success']);

const processing = ref(false);
const generalError = ref('');
const mp = ref(null);

const total = computed(() => {
  const subtotal = props.items.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
  return subtotal * 1.10;
});

const loadMercadoPagoSDK = () => {
  return new Promise((resolve) => {
    if (window.MercadoPago) return resolve(window.MercadoPago);
    const script = document.createElement('script');
    script.src = 'https://sdk.mercadopago.com/js/v2';
    script.onload = () => resolve(window.MercadoPago);
    document.head.appendChild(script);
  });
};

const renderBrick = async () => {
  if (!props.isOpen || total.value <= 0) {
      return; 
  }
  
  generalError.value = '';

  try {
    const MercadoPago = await loadMercadoPagoSDK();
    const keyData = await api.getMercadoPagoPublicKey();
    mp.value = new MercadoPago(keyData.public_key, { locale: 'es-PE' });

    const bricksBuilder = mp.value.bricks();

    const settings = {
      initialization: {
        amount: Number(total.value.toFixed(2)), 
      },
      customization: {
        visual: {
          style: { theme: 'dark' }
        },
        paymentMethods: {
          creditCard: "all",
          debitCard: "all"
        }
      },
      callbacks: {
        onReady: () => {
          console.log("Payment Brick principal construido con monto:", total.value);
        },
        onSubmit: async (formData) => {
          processing.value = true;
          generalError.value = '';
          
          try {
            formData.description = 'Compra en Funko Slayer';
            const result = await api.processDirectPayment(formData);

            if (result.success && result.status === 'approved') {
              emit('payment-success');
              close();
              alert('¡Pago procesado exitosamente!');
            } else {
              generalError.value = 'El pago fue rechazado. Verifica los datos de tu tarjeta.';
            }
          } catch (error) {
            generalError.value = 'Ocurrió un error al procesar el pago en el servidor.';
          } finally {
            processing.value = false;
          }
        },
        onError: (error) => {
          console.error(error);
          generalError.value = 'No se pudo cargar el módulo de pagos seguro.';
        },
      },
    };

    if (window.paymentBrickController) window.paymentBrickController.unmount();

    window.paymentBrickController = await bricksBuilder.create(
      'payment',
      'paymentBrick_container',
      settings
    );

  } catch (error) {
    console.error('Error inicializando MercadoPago:', error);
  }
};

watch([() => props.isOpen, () => total.value], ([newIsOpen, newTotal]) => {
  if (newIsOpen && newTotal > 0) {
    setTimeout(() => renderBrick(), 100);
  } else if (!newIsOpen) {
    if (window.paymentBrickController) window.paymentBrickController.unmount();
  }
});

const close = () => {
  generalError.value = '';
  emit('close');
};
</script>