<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <form @submit.prevent="handleRegister" class="w-full max-w-sm p-6 bg-white rounded-lg shadow">
      <h1 class="mb-4 text-xl font-bold text-center">Daftar SarprasHub</h1>

      <p v-if="errorMessage" class="mb-3 text-sm text-red-600">{{ errorMessage }}</p>
      <p v-if="successMessage" class="mb-3 text-sm text-green-600">{{ successMessage }}</p>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Nama</label>
        <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Email</label>
        <input v-model="form.email" type="email" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Password</label>
        <input v-model="form.password" type="password" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 text-sm font-medium">Konfirmasi Password</label>
        <input v-model="form.password_confirmation" type="password" required class="w-full px-3 py-2 border rounded" />
      </div>

      <button type="submit" :disabled="loading" class="w-full py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
        {{ loading ? 'Memproses...' : 'Daftar' }}
      </button>

      <p class="mt-4 text-sm text-center">
        Sudah punya akun?
        <router-link to="/login" class="text-blue-600 hover:underline">Login</router-link>
      </p>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

async function handleRegister() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.register(form);
    successMessage.value = 'Registrasi berhasil! Mengarahkan ke halaman login...';
    setTimeout(() => {
      router.push('/login');
    }, 1500);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Registrasi gagal, coba lagi.';
  } finally {
    loading.value = false;
  }
}
</script>
