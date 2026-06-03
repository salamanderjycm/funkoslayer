<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute top-10 right-10 w-72 h-72 bg-[#FF2A85] rounded-full filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-10 left-10 w-72 h-72 bg-cyan-500 rounded-full filter blur-[120px] opacity-10 animate-pulse"></div>

    <div class="max-w-md w-full bg-gray-900/80 backdrop-blur-xl border border-gray-800 rounded-2xl shadow-2xl p-8 z-10">
      
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black tracking-wider text-white italic">
          FUNKO <span class="text-[#FF2A85]">SLAYER</span>
        </h1>
        <p class="text-gray-400 text-sm mt-2">Únete a la cacería. Crea tu cuenta.</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Nombre de Cazador</label>
          <input 
            v-model="form.name"
            type="text" 
            required
            placeholder="Tu nombre o alias"
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FF2A85] transition-colors"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Correo Electrónico</label>
          <input 
            v-model="form.email"
            type="email" 
            required
            placeholder="ejemplo@correo.com"
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FF2A85] transition-colors"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Contraseña</label>
          <input 
            v-model="form.password"
            type="password" 
            required
            placeholder="••••••••"
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#FF2A85] transition-colors"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Confirmar Contraseña</label>
          <input 
            v-model="form.password_confirmation"
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
          class="w-full py-3.5 bg-gradient-to-r from-[#FF2A85] to-[#C2185B] text-white text-sm font-black uppercase tracking-wider rounded-xl shadow-lg shadow-[#FF2A85]/20 hover:opacity-90 disabled:opacity-50 transition-all"
        >
          {{ loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
        </button>
      </form>

      <div class="mt-8 text-center border-t border-gray-800/60 pt-6">
        <p class="text-sm text-gray-400">
          ¿Ya tienes cuenta? 
          <button @click="goToLogin" type="button" class="text-[#FF2A85] font-bold hover:underline ml-1">Inicia sesión aquí</button>
        </p>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

const emit = defineEmits(['register-success', 'go-login']);

const loading = ref(false);
const error = ref('');

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const handleRegister = async () => {
  error.value = '';

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden.';
    return;
  }

  loading.value = true;

  const result = await api.register(
    form.value.name,
    form.value.email,
    form.value.password,
    form.value.password_confirmation
  );

  if (result.success) {
    // Si la API devuelve el token (ya modificado en Laravel), iniciamos sesión en el acto
    emit('register-success', result.user);
  } else {
    error.value = result.message || 'El correo ya está en uso o los datos son inválidos.';
  }

  loading.value = false;
};

const goToLogin = () => {
  emit('go-login');
};
</script>