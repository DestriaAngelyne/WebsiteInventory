<template>
  <header class="sticky top-0 z-20 border-b border-border bg-background/80 backdrop-blur-sm">
    <div class="flex items-center justify-between gap-4 px-8 py-4">
      <div>
        <h1 class="text-lg font-bold text-foreground">{{ title }}</h1>
        <p class="text-sm text-muted-foreground">{{ subtitle }}</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="uiStore.openRequestModal()"
          class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-[#1d4ed8]"
        >
          <Plus class="w-4 h-4" />
          Ajukan Pinjam Barang
        </button>

        <router-link
          to="/notifikasi"
          class="relative flex items-center justify-center w-10 h-10 rounded-lg border border-border bg-card text-muted-foreground hover:text-foreground"
        >
          <Bell class="w-5 h-5" />
          <span
            v-if="notifCount > 0"
            class="absolute flex items-center justify-center w-5 h-5 text-[10px] font-semibold text-white bg-destructive rounded-full -right-1 -top-1"
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
import { Bell, Plus } from 'lucide-vue-next';
import { usePeminjamanStore } from '../../stores/peminjaman';
import { useUiStore } from '../../stores/ui';

defineProps({
  title: { type: String, default: 'Dashboard Saya' },
  subtitle: { type: String, default: 'Pantau status peminjaman sarpras kamu di sini.' },
});

const peminjamanStore = usePeminjamanStore();
const uiStore = useUiStore();

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
