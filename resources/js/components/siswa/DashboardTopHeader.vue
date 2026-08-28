<template>
  <header class="sticky top-0 z-10 bg-white border-b bg-opacity-90 backdrop-blur">
    <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
      <div>
        <h1 class="text-lg font-bold text-gray-800">{{ title }}</h1>
        <p class="text-sm text-gray-500">{{ subtitle }}</p>
      </div>

      <div class="flex items-center gap-3">
        <router-link
          to="/peminjaman/ajukan"
          class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700"
        >
          + Ajukan Pinjam Barang
        </router-link>

        <router-link
          to="/notifikasi"
          class="relative flex items-center justify-center w-10 h-10 text-gray-500 bg-white border rounded-lg hover:text-gray-800"
        >
          🔔
          <span
            v-if="notifCount > 0"
            class="absolute flex items-center justify-center w-5 h-5 text-[10px] font-semibold text-white bg-red-600 rounded-full -right-1 -top-1"
          >
            {{ notifCount }}
          </span>
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { usePeminjamanStore } from '../../stores/peminjaman';

defineProps({
  title: { type: String, default: 'Dashboard Saya' },
  subtitle: { type: String, default: 'Pantau status peminjaman sarpras kamu di sini.' },
});

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
</script>
