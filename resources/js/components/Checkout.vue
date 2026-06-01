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

// Cálculo del total (Asegúrate de que la lógica impositiva coincida con tu backend)
const total = computed(() => {
  const subtotal = props.items.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
  return subtotal * 1.10; // +10% de impuesto según tu lógica
});

// Función para inyectar el SDK de MercadoPago
const loadMercadoPagoSDK = () => {
  return new Promise((resolve) => {
    if (window.MercadoPago) return resolve(window.MercadoPago);
    const script = document.createElement('script');
    script.src = 'https://sdk.mercadopago.com/js/v2';
    script.onload = () => resolve(window.MercadoPago);
    document.head.appendChild(script);
  });
};

// Renderizado del Brick
const renderBrick = async () => {
  if (!props.isOpen) return;
  generalError.value = '';

  try {
    const MercadoPago = await loadMercadoPagoSDK();
    
    // Obtenemos tu llave pública (TEST-...) desde el backend
    const keyData = await api.getMercadoPagoPublicKey();
    mp.value = new MercadoPago(keyData.public_key, { locale: 'es-PE' });

    const bricksBuilder = mp.value.bricks();

    const settings = {
      initialization: {
        amount: total.value, // Obligatorio para Checkout API
      },
      customization: {
        visual: {
          style: { theme: 'dark' } // Combina perfecto con tu diseño
        },
        paymentMethods: {
          maxInstallments: 1 // Forzamos 1 sola cuota
        }
      },
      callbacks: {
        onReady: () => {
          // El formulario está listo para usarse
        },
        onSubmit: async (cardFormData) => {
          processing.value = true;
          generalError.value = '';
          
          try {
            // Añadimos la descripción que espera nuestro controlador
            cardFormData.description = 'Compra en Funko Slayer';

            // Enviamos el token al servidor
            const result = await api.processDirectPayment(cardFormData);

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

    // Desmontar el brick anterior si el usuario abrió y cerró el modal rápido
    if (window.cardPaymentBrickController) window.cardPaymentBrickController.unmount();

    window.cardPaymentBrickController = await bricksBuilder.create(
      'cardPayment',
      'paymentBrick_container',
      settings
    );

  } catch (error) {
    console.error('Error inicializando MercadoPago:', error);
  }
};

// Escuchamos cuando se abre el modal para renderizar la tarjeta
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    // Pequeño timeout para asegurar que el div exista en el DOM
    setTimeout(() => renderBrick(), 100);
  } else {
    if (window.cardPaymentBrickController) window.cardPaymentBrickController.unmount();
  }
});

const close = () => {
  generalError.value = '';
  emit('close');
};
</script>