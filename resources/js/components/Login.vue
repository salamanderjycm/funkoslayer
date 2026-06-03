<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute top-10 left-10 w-72 h-72 bg-[#FF2A85] rounded-full filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-cyan-500 rounded-full filter blur-[120px] opacity-10 animate-pulse"></div>

    <div class="max-w-md w-full bg-gray-900/80 backdrop-blur-xl border border-gray-800 rounded-2xl shadow-2xl p-8 z-10">
      
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black tracking-wider text-white italic">
          FUNKO <span class="text-[#FF2A85]">SLAYER</span>
        </h1>
        <p class="text-gray-400 text-sm mt-2">Inicia sesión para cazar tus figuras favoritas</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Correo Electrónico</label>
          <div class="relative">
            <input 
              v-model="email"
              type="email" 
              required
              placeholder="ejemplo@correo.com"
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FF2A85] transition-colors"
            />
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-400">Contraseña</label>
            <a href="#" class="text-xs text-gray-500 hover:text-[#FF2A85] transition-colors">¿La olvidaste?</a>
          </div>
          <input 
            v-model="password"
            type="password" 
            required
            placeholder="••••••••"
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FF2A85] transition-colors"
          />
        </div>

        <div v-if="error" class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-xl text-sm text-center">
          {{ error }}
        </div>

        <button 
          type="submit"
          :disabled="loading"
          class="w-full py-3.5 bg-gradient-to-r from-[#FF2A85] to-[#C2185B] text-white text-sm font-black uppercase tracking-wider rounded-xl shadow-lg shadow-[#FF2A85]/20 hover:opacity-90 active:scale-[0.99] disabled:opacity-50 transition-all"
        >
          {{ loading ? 'Conectando...' : 'Ingresar al Sistema' }}
        </button>
      </form>

      <div class="mt-8 text-center border-t border-gray-800/60 pt-6">
        <p class="text-sm text-gray-400">
          ¿Aún no tienes cuenta? 
          <button @click="goToRegister" type="button" class="text-[#FF2A85] font-bold hover:underline ml-1">
            Regístrate
          </button>
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

// Usamos los mismos emits que maneja tu componente padre
const emit = defineEmits(['login-success', 'go-register']);

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  error.value = '';
  loading.value = true;

  const result = await api.login(email.value, password.value);

  if (result.success) {
    emit('login-success', result.user);
  } else {
    error.value = result.message || 'Credenciales incorrectas. Intenta de nuevo.';
  }

  loading.value = false;
};

// Esta función dispara el evento para cambiar la vista al Registro
const goToRegister = () => {
  emit('go-register');
};
</script>