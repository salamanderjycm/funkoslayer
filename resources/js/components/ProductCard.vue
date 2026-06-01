¡Tienes toda la razón! Los colores neón (especialmente el cyan y el magenta) están literalmente diseñados para brillar sobre fondos oscuros. Ponerlos sobre blanco apaga mucho su efecto "tecnológico" o cyberpunk.

Vamos a transformar toda la tienda al "Modo Oscuro" (Dark Mode). Al hacer esto, los colores de tu logo van a resaltar increíblemente.

Aquí tienes los dos archivos actualizados. Cambié los fondos a tonos gray-900 y gray-800, y ajusté los textos grises para que sean legibles en la oscuridad.

1. components/ProductCard.vue (Modo Oscuro)
Copia y reemplaza todo en tu tarjeta. Cambié el fondo blanco por un gris muy oscuro, lo que hace que la foto del Funko y el botón destaquen mucho más.

HTML
<template>
  <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-cyan-500/20 transition-all duration-300 border-b-4 border-transparent hover:border-[#FF2A85]">
    <div class="bg-gray-900 h-48 flex items-center justify-center overflow-hidden">
      <img 
        v-if="product.image"
        :src="product.image.startsWith('http') ? product.image : '/storage/' + product.image" 
        :alt="product.name"
        class="w-full h-full object-cover hover:scale-110 transition-transform duration-500 opacity-90 hover:opacity-100"
      />
      <span v-else class="text-4xl">📦</span>
    </div>

    <div class="p-4 flex flex-col h-[calc(100%-12rem)]">
      <p class="text-xs text-[#4DF0FF] font-bold uppercase tracking-wider mb-1">
        {{ product.category?.name || 'Sin categoría' }}
      </p>
      <h4 class="text-lg font-bold mb-2 text-white leading-tight">{{ product.name }}</h4>
      <p class="text-sm text-gray-400 mb-4 line-clamp-2 flex-grow">{{ product.description }}</p>
      
      <div class="flex justify-between items-center mt-auto">
        <span class="text-2xl font-black text-[#FF2A85] drop-shadow-sm">
          ${{ parseFloat(product.price).toFixed(2) }}
        </span>
        
        <button
          @click="handleAddToCart"
          :disabled="product.stock <= 0"
          :class="product.stock <= 0 
            ? 'bg-gray-700 text-gray-500 cursor-not-allowed' 
            : 'bg-[#4DF0FF] hover:bg-[#00B3CC] text-slate-900 shadow-md shadow-cyan-500/40 hover:scale-105'"
          class="font-bold px-4 py-2 rounded-lg transition-all transform tracking-wide text-sm flex items-center gap-1 uppercase"
        >
          <span v-if="product.stock > 0">🛒</span>
          {{ product.stock > 0 ? 'Agregar' : 'Agotado' }}
        </button>
      </div>
      <p v-if="product.stock > 0" class="text-xs text-gray-500 mt-3 font-medium">
        Stock: <span class="font-bold text-gray-300">{{ product.stock }}</span>
      </p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['add-to-cart']);

const handleAddToCart = () => {
  if (props.product.stock > 0) {
    emit('add-to-cart', {
      id: props.product.id,
      name: props.product.name,
      price: props.product.price,
      image: props.product.image,
      category: props.product.category?.name
    });
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>