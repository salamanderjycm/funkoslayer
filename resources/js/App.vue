<template>
  <div>
    <div v-if="!user">
      <Login 
        v-if="currentAuthView === 'login'"
        @login-success="handleLoginSuccess"
        @go-register="currentAuthView = 'register'"
      />
      <Register 
        v-if="currentAuthView === 'register'"
        @register-success="handleRegisterSuccess"
        @go-login="currentAuthView = 'login'"
      />
    </div>

    <AdminPanel 
      v-else-if="user.role === 'admin'"
      :user="user"
      @logout="handleLogout"
    />

    <div v-else class="min-h-screen bg-gray-900 text-gray-200 flex flex-col">
      <header class="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <div class="flex items-center gap-3">
            <img :src="'/img/funkoslayerlogo.png'" alt="Logo Funko" class="w-10 h-10 object-contain">
            <img :src="'/img/funkoletras.png'" alt="Letras Funko Slayer" class="h-10 w-auto object-contain">
            <h1 class="hidden md:block text-2xl font-black italic text-transparent bg-clip-text bg-gradient-to-r from-[#FF2A85] to-[#4DF0FF] tracking-wider uppercase">
              Funko Slayer
            </h1>
          </div>
          <nav class="flex items-center space-x-6">
            <a href="#" @click.prevent="currentView = 'products'" 
               :class="currentView === 'products' ? 'text-[#4DF0FF] drop-shadow-sm font-bold' : 'text-gray-300 hover:text-[#FF2A85] transition-colors'">
              Inicio
            </a>
            <a href="#" @click.prevent="currentView = 'categories'" 
               :class="currentView === 'categories' ? 'text-[#4DF0FF] drop-shadow-sm font-bold' : 'text-gray-300 hover:text-[#FF2A85] transition-colors'">
              Categorías
            </a>
            
            <a href="#" @click.prevent="currentView = 'dashboard'" 
               :class="currentView === 'dashboard' ? 'text-[#4DF0FF] drop-shadow-sm font-bold' : 'text-gray-300 hover:text-[#FF2A85] transition-colors'">
              Mis Pedidos
            </a>

            <a href="#" @click.prevent="currentView = 'contact'" 
               :class="currentView === 'contact' ? 'text-[#4DF0FF] drop-shadow-sm font-bold' : 'text-gray-300 hover:text-[#FF2A85] transition-colors'">
              Contacto
            </a>
            
            <button 
              @click="toggleCart"
              class="relative p-2 text-gray-300 hover:text-[#4DF0FF] transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span v-if="cartCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-[#FF2A85] rounded-full shadow-lg shadow-pink-500/50">{{ cartCount }}</span>
            </button>
            <div class="border-l border-gray-600 pl-6 flex items-center space-x-3">
              <span class="text-sm text-gray-300">{{ user.name }}</span>
              <button
                @click="handleLogout"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm transition-colors shadow-md shadow-red-500/20"
              >
                Salir
              </button>
            </div>
          </nav>
        </div>
      </header>

      <section v-if="!['dashboard', 'contact', 'terms', 'refunds', 'privacy'].includes(currentView)" class="bg-gradient-to-r from-[#FF2A85] via-[#C2185B] to-[#4DF0FF] text-white py-12 border-b-4 border-gray-950">
        <div class="max-w-7xl mx-auto px-4 text-center">
          <h2 class="text-4xl font-black italic tracking-wide mb-4 drop-shadow-[0_2px_2px_rgba(0,0,0,0.8)] uppercase">Venta de Funkos</h2>
          <p class="text-xl font-medium drop-shadow-[0_1px_1px_rgba(0,0,0,0.8)]">Colecciona tus personajes favoritos</p>
        </div>
      </section>

      <section v-if="currentView === 'products'" class="max-w-7xl mx-auto px-4 py-12">
        <h3 class="text-3xl font-bold mb-8 text-white">Productos Destacados</h3>
        <div v-if="loading" class="text-center text-gray-400">
          <p>Cargando productos...</p>
        </div>
        <div v-if="selectedCategory" class="mb-4 flex items-center gap-3">
          <span class="text-lg text-gray-300">Categoría: <strong class="text-[#4DF0FF]">{{ selectedCategory.name }}</strong></span>
          <button @click="selectedCategory = null" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm transition-colors">
            ✕ Limpiar filtro
          </button>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <ProductCard 
            v-for="product in displayedProducts" 
            :key="product.id"
            :product="product"
            @add-to-cart="addToCart"
          />
        </div>
        <div v-if="selectedCategory" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <ProductCard 
            v-for="product in displayedProducts" 
            :key="product.id"
            :product="product"
            @add-to-cart="addToCart"
          />
        </div>
      </section>

      <section v-else-if="currentView === 'categories'" class="max-w-7xl mx-auto px-4 py-12">
        <h3 class="text-3xl font-bold mb-8 text-white">Categorías</h3>
        <div v-if="loadingCategories" class="text-center text-gray-400">
          <p>Cargando categorías...</p>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="category in categories" 
            :key="category.id"
            @click="selectCategory(category)"
            class="bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-cyan-500/20 transition-all duration-300 border-t-4 border-transparent hover:border-[#4DF0FF] cursor-pointer transform hover:scale-105"
          >
            <div class="bg-gray-900 h-40 flex items-center justify-center">
              <img v-if="category.image" :src="category.image" :alt="category.name" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity" />
              <span v-else class="text-4xl opacity-50">📦</span>
            </div>
            <div class="p-4">
              <h4 class="text-xl font-bold mb-2 text-white">{{ category.name }}</h4>
              <p class="text-gray-400 text-sm mb-4">{{ category.description }}</p>
              <p class="text-[#FF2A85] font-bold">{{ category.products?.length || 0 }} productos</p>
              <button class="mt-3 w-full bg-[#4DF0FF] hover:bg-[#00B3CC] text-slate-900 font-bold py-2 px-4 rounded-lg transition-colors uppercase text-sm">
                Ver Productos →
              </button>
            </div>
          </div>
        </div>
      </section>

      <CustomerDashboard v-else-if="currentView === 'dashboard'" />

      <section v-else-if="currentView === 'contact'" class="max-w-4xl mx-auto px-4 py-16 text-gray-300">
        <h2 class="text-3xl font-black italic text-white mb-6 uppercase border-b border-gray-800 pb-4">Contacto y Atención al Cliente</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
          <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <h3 class="text-[#4DF0FF] font-bold text-xl mb-4">Información Directa</h3>
            <p class="mb-2"><strong>📍 Ubicación:</strong> Anta, Cusco, Perú</p>
            <p class="mb-2"><strong>📧 Correo:</strong> soporte@funko.blog</p>
            <p class="mb-2"><strong>📱 WhatsApp:</strong> +51 999 888 777</p>
            <p class="mt-4 text-sm text-gray-500">Horario de atención: Lunes a Viernes de 9:00 AM a 6:00 PM.</p>
          </div>
          <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <h3 class="text-[#FF2A85] font-bold text-xl mb-4">¿Dudas con tu pedido?</h3>
            <p class="text-sm leading-relaxed">
              Si tuviste algún inconveniente con tu pago vía Mercado Pago o Yape, o quieres saber el estado de tu envío a provincia, escríbenos directamente a nuestro WhatsApp indicando tu número de Orden (lo encuentras en la sección "Mis Pedidos").
            </p>
          </div>
        </div>
      </section>

      <section v-else-if="currentView === 'terms'" class="max-w-4xl mx-auto px-4 py-16 text-gray-300">
        <h2 class="text-3xl font-black italic text-white mb-6 uppercase border-b border-gray-800 pb-4">Términos y Condiciones</h2>
        <div class="space-y-6 text-sm leading-relaxed">
          <p>Bienvenido a Funko Slayer. Al utilizar nuestra plataforma y realizar compras, aceptas los siguientes términos aplicables en el territorio peruano.</p>
          <h3 class="text-[#4DF0FF] font-bold text-lg mt-4">1. Pagos y Transacciones</h3>
          <p>Todos los pagos son procesados de forma segura a través de Mercado Pago. Aceptamos tarjetas de crédito, débito y transferencias vía Yape. Funko Slayer no almacena datos financieros ni números de tarjeta en sus servidores.</p>
          <h3 class="text-[#4DF0FF] font-bold text-lg mt-4">2. Disponibilidad de Stock</h3>
          <p>El inventario se actualiza en tiempo real. En el improbable caso de que se apruebe una compra de un producto sin stock debido a un error del sistema, nos comunicaremos inmediatamente para ofrecer un reemplazo o la devolución del 100% de su dinero.</p>
          <h3 class="text-[#4DF0FF] font-bold text-lg mt-4">3. Envíos y Entregas</h3>
          <p>Realizamos envíos a nivel nacional desde Cusco. Los tiempos de entrega varían según la agencia de transporte (Shalom, Olva Courier, etc.) y la provincia de destino. El número de seguimiento será proporcionado una vez despachado el paquete.</p>
        </div>
      </section>

      <section v-else-if="currentView === 'refunds'" class="max-w-4xl mx-auto px-4 py-16 text-gray-300">
        <h2 class="text-3xl font-black italic text-white mb-6 uppercase border-b border-gray-800 pb-4">Políticas de Devolución y Reembolso</h2>
        <div class="space-y-6 text-sm leading-relaxed">
          <p>En Funko Slayer nos aseguramos de que cada figura llegue en perfectas condiciones a tu colección. Sin embargo, si surge algún inconveniente, aplicamos las siguientes políticas en cumplimiento con el Código de Protección y Defensa del Consumidor (INDECOPI):</p>
          <ul class="list-disc pl-5 space-y-2 mt-4 text-gray-400">
            <li><strong>Productos Dañados:</strong> Si el Funko llega con daños de fábrica o la caja está seriamente afectada por el transporte, tienes un plazo de 48 horas desde la recepción para reportarlo a nuestro correo o WhatsApp con fotografías como evidencia.</li>
            <li><strong>Reembolsos:</strong> Los reembolsos por cancelaciones aprobadas se procesarán a través del mismo medio de pago utilizado (Mercado Pago) o vía transferencia interbancaria. El dinero puede tardar entre 2 a 15 días hábiles en reflejarse en tu cuenta bancaria, dependiendo de las políticas de tu banco.</li>
            <li><strong>Excepciones:</strong> No se aceptan devoluciones por "cambio de opinión" una vez que el producto ha sido abierto o extraído de su empaque original protector.</li>
          </ul>
        </div>
      </section>

      <section v-else-if="currentView === 'privacy'" class="max-w-4xl mx-auto px-4 py-16 text-gray-300">
        <h2 class="text-3xl font-black italic text-white mb-6 uppercase border-b border-gray-800 pb-4">Políticas de Privacidad</h2>
        <div class="space-y-6 text-sm leading-relaxed">
          <p>En cumplimiento de la Ley N° 29733, Ley de Protección de Datos Personales, Funko Slayer garantiza la seguridad y confidencialidad de la información proporcionada por nuestros clientes.</p>
          <p><strong>Uso de la Información:</strong> Los datos personales (nombre, correo, dirección) se utilizan exclusivamente para procesar tus pedidos, emitir comprobantes de pago y coordinar el envío de tus productos. No vendemos ni compartimos tu información con terceros no relacionados con la logística de entrega.</p>
        </div>
      </section>

      <footer class="bg-gray-950 border-t border-gray-900 pt-12 pb-8 mt-auto w-full">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          
          <div class="col-span-1 md:col-span-1">
            <h3 class="text-xl font-black italic text-transparent bg-clip-text bg-gradient-to-r from-[#FF2A85] to-[#4DF0FF] uppercase mb-4">Funko Slayer</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              Tu tienda especializada en figuras de colección. Cazamos los Funkos más exclusivos para que los tengas en tu repisa.
            </p>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Enlaces Rápidos</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="#" @click.prevent="currentView = 'products'" class="hover:text-[#4DF0FF] transition-colors">Tienda</a></li>
              <li><a href="#" @click.prevent="currentView = 'categories'" class="hover:text-[#4DF0FF] transition-colors">Categorías</a></li>
              <li><a href="#" @click.prevent="currentView = 'dashboard'" class="hover:text-[#4DF0FF] transition-colors">Mis Pedidos</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Legales</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="#" @click.prevent="currentView = 'terms'" class="hover:text-[#FF2A85] transition-colors">Términos y Condiciones</a></li>
              <li><a href="#" @click.prevent="currentView = 'privacy'" class="hover:text-[#FF2A85] transition-colors">Políticas de Privacidad</a></li>
              <li><a href="#" @click.prevent="currentView = 'refunds'" class="hover:text-[#FF2A85] transition-colors">Políticas de Devolución</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Atención al Cliente</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="#" @click.prevent="currentView = 'contact'" class="hover:text-cyan-400 transition-colors flex items-center gap-2"><span>💬</span> Contáctanos</a></li>
              <li class="pt-2">
                <img src="https://logospng.org/download/mercado-pago/logo-mercado-pago-icono-1024.png" alt="Mercado Pago" class="h-6 opacity-70 grayscale hover:grayscale-0 transition-all">
              </li>
            </ul>
          </div>

        </div>
        
        <div class="max-w-7xl mx-auto px-4 pt-8 border-t border-gray-900 text-center text-xs text-gray-600 font-mono">
          &copy; {{ new Date().getFullYear() }} Funko Slayer. Todos los derechos reservados. Desarrollado en Perú.
        </div>
      </footer>

    </div>

    <ShoppingCart 
      :isOpen="showCart"
      :items="cart"
      @close="closeCart"
      @update-quantity="updateCartItemQuantity"
      @remove-item="removeCartItem"
      @checkout="handleCheckout"
    />

    <Checkout 
      :isOpen="showCheckout"
      :items="cart"
      @close="closeCheckout"
      @payment-success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import ProductCard from './components/ProductCard.vue';
import Login from './components/Login.vue';
import Register from './components/Register.vue';
import AdminPanel from './components/AdminPanel.vue';
import ShoppingCart from './components/ShoppingCart.vue';
import Checkout from './components/Checkout.vue';
import CustomerDashboard from './components/CustomerDashboard.vue';
import { api } from './services/api.js';

// State
const user = ref(null);
const products = ref([]);
const categories = ref([]);
const cart = ref([]);
const showCart = ref(false);
const showCheckout = ref(false);
const loading = ref(false);
const loadingCategories = ref(false);
const currentView = ref('products');
const currentAuthView = ref('login');
const selectedCategory = ref(null);

// Computed
const cartCount = computed(() => {
  return cart.value.reduce((total, item) => total + item.quantity, 0);
});

const displayedProducts = computed(() => {
  if (selectedCategory.value) {
    return products.value.filter(product => product.category_id === selectedCategory.value.id);
  }
  return products.value;
});

// Methods
const loadProducts = async () => {
  loading.value = true;
  products.value = await api.getProducts();
  loading.value = false;
};

const loadCategories = async () => {
  loadingCategories.value = true;
  categories.value = await api.getCategories();
  loadingCategories.value = false;
};

const handleLoginSuccess = (userData) => {
  user.value = userData;
  currentAuthView.value = 'login'; // Reset
};

const handleRegisterSuccess = (userData) => {
  user.value = userData;
  currentAuthView.value = 'login'; // Reset
};

const handleLogout = async () => {
  await api.logout();
  user.value = null;
  cart.value = [];
  currentAuthView.value = 'login';
};

const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id);
  
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.value.push({
      ...product,
      quantity: 1
    });
  }
  
  console.log(`${product.name} agregado al carrito`);
};

const toggleCart = () => {
  showCart.value = !showCart.value;
};

const closeCart = () => {
  showCart.value = false;
};

const updateCartItemQuantity = (data) => {
  const item = cart.value.find(cartItem => cartItem.id === data.id);
  if (item) {
    item.quantity += data.quantity;
    
    // Eliminar si la cantidad llega a 0
    if (item.quantity <= 0) {
      removeCartItem(data.id);
    }
  }
};

const removeCartItem = (itemId) => {
  cart.value = cart.value.filter(item => item.id !== itemId);
};

const handleCheckout = () => {
  if (cart.value.length === 0) {
    alert('Tu carrito está vacío');
    return;
  }
  
  // Abrir modal de checkout
  showCheckout.value = true;
  showCart.value = false;
};

const closeCheckout = () => {
  showCheckout.value = false;
};

const handlePaymentSuccess = (paymentData) => {
  // Vaciar el carrito después del pago exitoso
  cart.value = [];
  showCheckout.value = false;
  
  // Mostrar mensaje de éxito
  alert(`Pedido completado exitosamente!\nMonto: $${paymentData.amount.toFixed(2)}`);
  
  // Volver a la vista de productos
  currentView.value = 'products';
};

const selectCategory = (category) => {
  selectedCategory.value = category;
  currentView.value = 'products';
};

// Lifecycle
onMounted(async () => {
  if (api.isAuthenticated()) {
    const result = await api.getMe();
    if (result.success && result.user) {
      user.value = result.user;
    } else {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
  }
  
  loadProducts();
  loadCategories();
});
</script>

<style scoped>
/* App styles */
</style>