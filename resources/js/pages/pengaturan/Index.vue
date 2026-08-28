<template>
  <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6">
    <div class="mb-8">
      <router-link
        to="/profil"
        class="inline-flex items-center gap-1 mb-3 text-xs font-medium text-gray-500 hover:text-gray-700"
      >
        ← Kembali ke Profil
      </router-link>
      <h1 class="text-xl font-bold text-gray-900">Pengaturan</h1>
      <p class="mt-1 text-sm text-gray-500">Kelola keamanan akun kamu.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      <!-- Kolom kiri: konteks -->
      <div class="lg:col-span-1">
        <div class="flex items-center justify-center w-10 h-10 mb-3 text-blue-600 bg-blue-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-800">Keamanan Akun</h2>
        <p class="mt-1 text-sm leading-relaxed text-gray-500">
          Perbarui password secara berkala dan jangan bagikan ke siapa pun untuk menjaga akun kamu tetap aman.
        </p>
      </div>

      <!-- Kolom kanan: form -->
      <div class="lg:col-span-2">
        <div class="p-6 bg-white border border-gray-200 rounded-xl">
          <p v-if="passwordError" class="px-3 py-2 mb-4 text-xs text-red-700 bg-red-50 rounded-lg">
            {{ passwordError }}
          </p>
          <p v-if="passwordSuccess" class="px-3 py-2 mb-4 text-xs text-green-700 bg-green-50 rounded-lg">
            {{ passwordSuccess }}
          </p>

          <div class="space-y-5">
            <div>
              <label class="block mb-1.5 text-xs font-medium text-gray-600">Password Saat Ini</label>
              <div class="relative">
                <input
                  v-model="passwordForm.current_password"
                  :type="showCurrent ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button type="button" @click="showCurrent = !showCurrent" class="absolute -translate-y-1/2 right-3 top-1/2 text-gray-400 hover:text-gray-600">
                  <EyeIcon :open="showCurrent" />
                </button>
              </div>
            </div>

            <div>
              <label class="block mb-1.5 text-xs font-medium text-gray-600">Password Baru</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password"
                  :type="showNew ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button type="button" @click="showNew = !showNew" class="absolute -translate-y-1/2 right-3 top-1/2 text-gray-400 hover:text-gray-600">
                  <EyeIcon :open="showNew" />
                </button>
              </div>
              <p class="mt-1.5 text-xs text-gray-400">Minimal 8 karakter.</p>
            </div>

            <div>
              <label class="block mb-1.5 text-xs font-medium text-gray-600">Konfirmasi Password Baru</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password_confirmation"
                  :type="showConfirm ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button type="button" @click="showConfirm = !showConfirm" class="absolute -translate-y-1/2 right-3 top-1/2 text-gray-400 hover:text-gray-600">
                  <EyeIcon :open="showConfirm" />
                </button>
              </div>
              <p
                v-if="passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation"
                class="mt-1.5 text-xs text-red-500"
              >
                Konfirmasi tidak sama dengan password baru.
              </p>
            </div>
          </div>

          <div class="flex justify-end pt-5 mt-6 border-t border-gray-100">
            <button
              @click="handleChangePassword"
              :disabled="passwordLoading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ passwordLoading ? 'Menyimpan...' : 'Ganti Password' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { h, reactive, ref } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

// Ikon mata (tampil/sembunyi password), tanpa dependency tambahan
const EyeIcon = (props) =>
  h(
    'svg',
    {
      xmlns: 'http://www.w3.org/2000/svg',
      class: 'w-4 h-4',
      viewBox: '0 0 24 24',
      fill: 'none',
      stroke: 'currentColor',
      'stroke-width': '2',
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
    },
    props.open
      ? [
          h('path', { d: 'M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.62 21.62 0 0 1 5.06-6.06' }),
          h('path', { d: 'M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.4 3.61' }),
          h('path', { d: 'M14.12 14.12a3 3 0 1 1-4.24-4.24' }),
          h('line', { x1: 1, y1: 1, x2: 23, y2: 23 }),
        ]
      : [
          h('path', { d: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z' }),
          h('circle', { cx: 12, cy: 12, r: 3 }),
        ]
  );

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const passwordLoading = ref(false);
const passwordError = ref('');
const passwordSuccess = ref('');

async function handleChangePassword() {
  passwordLoading.value = true;
  passwordError.value = '';
  passwordSuccess.value = '';

  try {
    await authStore.changePassword(passwordForm);
    passwordSuccess.value = 'Password berhasil diperbarui.';
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    passwordError.value = error.response?.data?.message || 'Gagal mengganti password.';
  } finally {
    passwordLoading.value = false;
  }
}
</script>
