<template>
  <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6">
    <div class="mb-8">
      <router-link
        to="/profil"
        class="inline-flex items-center gap-1 mb-3 text-xs font-medium text-muted-foreground hover:text-foreground"
      >
        ← Kembali ke Profil
      </router-link>
      <h1 class="text-xl font-bold text-foreground">Pengaturan</h1>
      <p class="mt-1 text-sm text-muted-foreground">Kelola keamanan akun kamu.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      <!-- Kolom kiri: konteks -->
      <div class="lg:col-span-1">
        <div class="flex items-center justify-center w-10 h-10 mb-3 rounded-lg bg-secondary text-primary">
          <Lock class="w-5 h-5" />
        </div>
        <h2 class="text-sm font-semibold text-foreground">Keamanan Akun</h2>
        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">
          Perbarui password secara berkala dan jangan bagikan ke siapa pun untuk menjaga akun kamu tetap aman.
        </p>
      </div>

      <!-- Kolom kanan: form -->
      <div class="lg:col-span-2">
        <div class="p-6 border rounded-xl border-border bg-card">
          <p v-if="passwordError" class="px-3 py-2 mb-4 text-xs rounded-lg text-danger-foreground bg-danger">
            {{ passwordError }}
          </p>
          <p v-if="passwordSuccess" class="px-3 py-2 mb-4 text-xs rounded-lg text-success-foreground bg-success">
            {{ passwordSuccess }}
          </p>

          <div class="space-y-5">
            <div>
              <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Password Saat Ini</label>
              <div class="relative">
                <input
                  v-model="passwordForm.current_password"
                  :type="showCurrent ? 'text' : 'password'"
                  class="w-full rounded-lg border border-border px-3 py-2 pr-10 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
                <button type="button" @click="showCurrent = !showCurrent" class="absolute -translate-y-1/2 right-3 top-1/2 text-muted-foreground hover:text-foreground">
                  <component :is="showCurrent ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div>
              <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Password Baru</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password"
                  :type="showNew ? 'text' : 'password'"
                  class="w-full rounded-lg border border-border px-3 py-2 pr-10 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
                <button type="button" @click="showNew = !showNew" class="absolute -translate-y-1/2 right-3 top-1/2 text-muted-foreground hover:text-foreground">
                  <component :is="showNew ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
              <p class="mt-1.5 text-xs text-muted-foreground">Minimal 8 karakter.</p>
            </div>

            <div>
              <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Konfirmasi Password Baru</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password_confirmation"
                  :type="showConfirm ? 'text' : 'password'"
                  class="w-full rounded-lg border border-border px-3 py-2 pr-10 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
                <button type="button" @click="showConfirm = !showConfirm" class="absolute -translate-y-1/2 right-3 top-1/2 text-muted-foreground hover:text-foreground">
                  <component :is="showConfirm ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
              <p
                v-if="passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation"
                class="mt-1.5 text-xs text-danger-foreground"
              >
                Konfirmasi tidak sama dengan password baru.
              </p>
            </div>
          </div>

          <div class="flex justify-end pt-5 mt-6 border-t border-border">
            <button
              @click="handleChangePassword"
              :disabled="passwordLoading"
              class="px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8] disabled:opacity-50"
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
import { reactive, ref } from 'vue';
import { Eye, EyeOff, Lock } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

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
