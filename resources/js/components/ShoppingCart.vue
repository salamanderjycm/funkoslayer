<template>
  <!-- Overlay muy ligero o casi invisible -->
  <div v-if="isOpen" class="fixed inset-0 z-40 transition-opacity duration-300" @click="close" style="background-color: rgba(0,0,0,0.01);"></div>

  <!-- Modal del carrito -->
  <div v-if="isOpen" class="fixed right-0 top-0 h-full w-full max-w-md bg-gray-900 shadow-2xl z-50 flex flex-col border-l-4 border-[#FF2A85] overflow-hidden">
    
    <!-- Header del carrito -->
    <div class="bg-gradient-to-r from-[#FF2A85] to-[#C2185B] p-6 flex justify-between items-center">
      <h2 class="text-2xl font-black text-white italic tracking-wide">🛒 Carrito</h2>
      <button 
        @click="close"
        class="text-white hover:text-gray-200 transition-colors text-2xl font-bold"
      >
        ✕
      </button>
    </div>

    <!-- Contenido del carrito -->
    <div class="flex-1 overflow-y-auto p-6">
      <!-- Carrito vacío -->
      <div v-if="items.length === 0" class="flex flex-col items-center justify-center h-full">
        <div class="text-6xl mb-4">🛒</div>
        <p class="text-gray-400 text-lg font-semibold">Tu carrito está vacío</p>
        <p class="text-gray-500 text-sm mt-2">Agrega productos para comenzar</p>
      </div>

      <!-- Lista de productos -->
      <div v-else class="space-y-4">
        <div 
          v-for="item in items" 
          :key="item.id"
          class="bg-gray-800 rounded-lg p-4 hover:bg-gray-750 transition-colors border border-gray-700"
        >
          <!-- Imagen y detalles -->
          <div class="flex gap-3 mb-3">
            <div class="w-16 h-16 bg-gray-900 rounded-lg overflow-hidden flex-shrink-0">
              <img 
                v-if="item.image"
                :src="item.image.startsWith('http') ? item.image : '/storage/' + item.image"
                :alt="item.name"
                class="w-full h-full object-cover"
              />
              <span v-else class="w-full h-full flex items-center justify-center text-xl">📦</span>
            </div>
            
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-bold text-white truncate">{{ item.name }}</h3>
              <p class="text-xs text-[#4DF0FF] font-semibold mt-1">{{ item.category }}</p>
              <p class="text-[#FF2A85] font-black text-sm mt-2">${{ parseFloat(item.price).toFixed(2) }}</p>
            </div>

            <!-- Botón eliminar -->
            <button
              @click="removeItem(item.id)"
              class="text-gray-500 hover:text-red-500 transition-colors font-bold text-lg"
              title="Eliminar del carrito"
            >
              🗑️
            </button>
          </div>

          <!-- Controles de cantidad -->
          <div class="flex items-center justify-between bg-gray-900 rounded-lg p-2">
            <button
              @click="decrementQuantity(item.id)"
              class="w-8 h-8 flex items-center justify-center bg-gray-700 hover:bg-gray-600 text-white rounded transition-colors text-sm font-bold"
            >
              −
            </button>
            
            <span class="text-white font-bold text-sm">
              {{ item.quantity }}
            </span>
            
            <button
              @click="incrementQuantity(item.id)"
              class="w-8 h-8 flex items-center justify-center bg-gray-700 hover:bg-gray-600 text-white rounded transition-colors text-sm font-bold"
            >
              +
            </button>

            <!-- Subtotal -->
            <span class="ml-auto text-[#4DF0FF] font-bold text-sm">
              ${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer con total y botones -->
    <div v-if="items.length > 0" class="bg-gray-800 border-t border-gray-700 p-6 space-y-4">
      <!-- Resumen -->
      <div class="space-y-2 bg-gray-900 rounded-lg p-4">
        <div class="flex justify-between text-gray-400 text-sm">
          <span>Subtotal:</span>
          <span>${{ subtotal.toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-gray-400 text-sm border-t border-gray-700 pt-2">
          <span>Cantidad de items:</span>
          <span>{{ totalItems }}</span>
        </div>
        <div class="flex justify-between text-white font-black text-lg border-t border-gray-700 pt-2 mt-2">
          <span>Total:</span>
          <span class="text-[#FF2A85]">${{ subtotal.toFixed(2) }}</span>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="space-y-2">
        <button
          @click="continueShopping"
          class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 uppercase tracking-wide"
        >
          ← Continuar Comprando
        </button>
        
        <button
          @click="proceedToCheckout"
          class="w-full bg-gradient-to-r from-[#4DF0FF] to-[#00B3CC] hover:from-[#00B3CC] hover:to-[#0099AA] text-slate-900 font-black py-3 px-4 rounded-lg transition-all duration-200 uppercase tracking-wide shadow-lg shadow-cyan-500/40 hover:scale-105 transform"
        >
          💳 Proceder al Pago →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

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

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'checkout']);

// Computed
const totalItems = computed(() => {
  return props.items.reduce((total, item) => total + item.quantity, 0);
});

const subtotal = computed(() => {
  return props.items.reduce((total, item) => {
    return total + (parseFloat(item.price) * item.quantity);
  }, 0);
});

// Methods
const close = () => {
  emit('close');
};

const continueShopping = () => {
  close();
};

const proceedToCheckout = () => {
  emit('checkout');
};

const incrementQuantity = (itemId) => {
  emit('update-quantity', { id: itemId, quantity: 1 });
};

const decrementQuantity = (itemId) => {
  emit('update-quantity', { id: itemId, quantity: -1 });
};

const removeItem = (itemId) => {
  emit('remove-item', itemId);
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
