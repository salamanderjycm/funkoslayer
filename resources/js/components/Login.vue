<template>
  <div class="min-h-screen bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8">
      <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Funko Slayer</h2>

      <form @submit.prevent="handleLogin" class="space-y-6">
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

        <!-- Error Message -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <!-- Register Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600">¿No tienes cuenta?</p>
        <button
          @click="goToRegister"
          class="text-purple-600 hover:text-purple-700 font-semibold"
        >
          Regístrate aquí
        </button>
      </div>

      <!-- Demo Credentials -->
      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm font-semibold text-blue-800 mb-2">Credenciales de prueba:</p>
        <p class="text-sm text-blue-700"><strong>Admin:</strong> admin@funkos.com / password123</p>
        <p class="text-sm text-blue-700"><strong>Cliente:</strong> customer@funkos.com / password123</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../services/api.js';

const emit = defineEmits(['login-success', 'go-register']);

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  error.value = '';
  loading.value = true;

  try {
    // ✅ SOLUCIÓN: Agregamos .value para acceder correctamente a los datos del ref
    const result = await api.login(form.value.email, form.value.password);

    if (result.success) {
      emit('login-success', result.user);
    } else {
      // Muestra el error exacto que devuelva tu servidor o un mensaje por defecto
      error.value = result.message || 'Error al iniciar sesión. Revisa tus credenciales.';
    }
  } catch (err) {
    // Un bloque catch es buena práctica por si falla la conexión (ej. servidor caído)
    console.error("Error en petición:", err);
    error.value = 'Ocurrió un problema al conectar con el servidor.';
  } finally {
    loading.value = false;
  }
};

const goToRegister = () => {
  emit('go-register');
};
</script>

<style scoped>
/* Login styles */
</style>