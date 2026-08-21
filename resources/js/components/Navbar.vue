<template>
  <nav class="flex items-center justify-between p-4 bg-white border-b">
    <router-link to="/" class="font-bold">SarprasHub</router-link>

    <div v-if="authStore.isLoggedIn" class="flex items-center gap-4 text-sm">
      <router-link v-if="authStore.isAdmin" to="/admin/barang" class="hover:underline">
        Data Barang
      </router-link>
      <router-link v-if="authStore.isAdmin" to="/admin/peminjaman" class="hover:underline">
        Kelola Peminjaman
      </router-link>
      <router-link v-if="authStore.isAdmin" to="/admin/users" class="hover:underline">
        Manajemen User
      </router-link>
      <router-link v-if="!authStore.isAdmin" to="/peminjaman" class="hover:underline">
        Peminjaman Saya
      </router-link>
      <router-link v-if="!authStore.isAdmin" to="/notifikasi" class="relative hover:underline">
        🔔
        <span
          v-if="notifCount > 0"
          class="absolute flex items-center justify-center w-4 h-4 text-xs text-white bg-red-600 rounded-full -top-2 -right-2"
        >
          {{ notifCount }}
        </span>
      </router-link>
      <span>{{ authStore.user?.name }} ({{ authStore.user?.role }})</span>
      <button @click="handleLogout" class="text-red-600 hover:underline">
        Logout
      </button>
    </div>
    <div v-else class="flex gap-4 text-sm">
      <router-link to="/login" class="hover:underline">Login</router-link>
      <router-link to="/register" class="hover:underline">Daftar</router-link>
    </div>
  </nav>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { usePeminjamanStore } from '../stores/peminjaman';

const router = useRouter();
const authStore = useAuthStore();
const peminjamanStore = usePeminjamanStore();

onMounted(() => {
  if (authStore.isLoggedIn && !authStore.isAdmin) {
    peminjamanStore.fetchMyPeminjaman();
  }
});

const notifCount = computed(() => {
  let count = 0;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  for (const item of peminjamanStore.myItems) {
    if (item.status === 'pending' || item.status === 'ditolak') count++;
    if (item.status_perpanjangan === 'disetujui' || item.status_perpanjangan === 'ditolak') count++;
    if (item.status === 'selesai' && (item.denda > 0 || item.biaya_ganti > 0)) count++;

    if (item.status === 'disetujui') {
      const rencana = new Date(item.tanggal_kembali_rencana);
      rencana.setHours(0, 0, 0, 0);
      if (today >= rencana) count++;
    }
  }

  return count;
});

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}
</script>
