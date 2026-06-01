<template>
  <div class="min-h-screen bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8">
      <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Crear Cuenta</h2>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
            placeholder="Tu nombre"
            required
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
            placeholder="tu@email.com"
            required
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
            placeholder="••••••••"
            required
          />
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
            placeholder="••••••••"
            required
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
          {{ error }}
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>
      </form>

      <!-- Login Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600">¿Ya tienes cuenta?</p>
        <button
          @click="goToLogin"
          class="text-purple-600 hover:text-purple-700 font-semibold"
        >
          Inicia sesión aquí
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

const emit = defineEmits(['register-success', 'go-login']);

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
  error.value = '';

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden';
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
    emit('register-success', result.user);
  } else {
    error.value = result.message || 'Error en registro';
  }

  loading.value = false;
};

const goToLogin = () => {
  emit('go-login');
};
</script>

<style scoped>
/* Register styles */
</style>
