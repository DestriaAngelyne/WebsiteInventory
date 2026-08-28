<template>
  <aside class="flex flex-col h-screen border-r bg-white w-60 shrink-0 sticky top-0">
    <div class="flex items-center gap-3 px-5 py-6">
      <div class="flex items-center justify-center w-9 h-9 text-white bg-blue-600 rounded-lg">
        📦
      </div>
      <div class="leading-tight">
        <p class="text-sm font-bold text-gray-800">SarprasHub</p>
        <span class="inline-block px-2 py-0.5 text-[10px] font-medium text-blue-700 bg-blue-100 rounded-full">
          Portal Siswa
        </span>
      </div>
    </div>

    <nav class="flex-1 px-3 py-2 overflow-y-auto">
      <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Menu</p>
      <ul class="flex flex-col gap-1">
        <li v-for="item in navItems" :key="item.to">
          <router-link
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive(item.to) ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'"
          >
            <span>{{ item.icon }}</span>
            <span class="flex-1">{{ item.label }}</span>
            <span
              v-if="item.badge && item.badge > 0"
              class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-semibold text-white bg-red-600 rounded-full"
            >
              {{ item.badge }}
            </span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="p-3">
      <div class="p-3 border rounded-xl">
        <div class="flex items-center gap-3">
          <img
            v-if="authStore.user?.avatar"
            :src="`/storage/${authStore.user.avatar}`"
            alt="Avatar"
            class="object-cover w-10 h-10 rounded-full"
          />
          <div v-else class="flex items-center justify-center w-10 h-10 font-bold text-gray-500 bg-gray-200 rounded-full">
            {{ authStore.user?.name?.charAt(0) }}
          </div>
          <div class="min-w-0 leading-tight">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ authStore.user?.kelas || '-' }}</p>
          </div>
        </div>

        <div class="px-3 py-2 mt-3 rounded-lg bg-gray-50">
          <p class="text-[10px] uppercase tracking-wide text-gray-400">NISN</p>
          <p class="text-xs font-medium text-gray-700">{{ authStore.user?.nisn || '-' }}</p>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="flex items-center justify-center w-full gap-2 px-3 py-2 mt-3 text-sm font-medium text-gray-500 border rounded-lg hover:border-red-400 hover:text-red-600"
        >
          Keluar / Logout
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { usePeminjamanStore } from '../../stores/peminjaman';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const peminjamanStore = usePeminjamanStore();

onMounted(() => {
  if (peminjamanStore.myItems.length === 0) {
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

const navItems = computed(() => [
  { label: 'Dashboard Saya', icon: '🏠', to: '/' },
  { label: 'Riwayat Peminjaman', icon: '📜', to: '/peminjaman' },
  { label: 'Pengajuan Baru', icon: '➕', to: '/peminjaman/ajukan' },
  { label: 'Notifikasi & Status', icon: '🔔', to: '/notifikasi', badge: notifCount.value },
  { label: 'Pengaduan', icon: '💬', to: '/pengaduan' },
  { label: 'Profil Saya', icon: '👤', to: '/profil' },
  { label: 'Pengaturan', icon: '⚙️', to: '/pengaturan' },
]);

function isActive(to) {
  return route.path === to;
}

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}
</script>
