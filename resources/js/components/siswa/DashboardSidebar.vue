<template>
  <aside class="sticky top-0 flex flex-col h-screen border-r border-border bg-sidebar w-60 shrink-0">
    <div class="flex items-center gap-3 px-5 py-6">
      <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary text-primary-foreground">
        <Boxes class="w-5 h-5" />
      </div>
      <div class="leading-tight">
        <p class="text-sm font-bold text-sidebar-foreground">SarprasHub</p>
        <span class="inline-block px-2 py-0.5 text-[10px] font-medium text-secondary-foreground bg-secondary rounded-full">
          Portal Siswa
        </span>
      </div>
    </div>

    <nav class="flex-1 px-3 py-2 overflow-y-auto">
      <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">Menu</p>
      <ul class="flex flex-col gap-1">
        <li v-for="item in navItems" :key="item.to">
          <router-link
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive(item.to) ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-muted hover:text-sidebar-foreground'"
          >
            <component :is="item.icon" class="w-[18px] h-[18px]" />
            <span class="flex-1">{{ item.label }}</span>
            <span
              v-if="item.badge && item.badge > 0"
              class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-semibold text-white bg-destructive rounded-full"
            >
              {{ item.badge }}
            </span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="p-3">
      <div class="p-3 border rounded-xl border-border bg-card">
        <div class="flex items-center gap-3">
          <img
            v-if="authStore.user?.avatar"
            :src="`/storage/${authStore.user.avatar}`"
            alt="Avatar"
            class="object-cover w-10 h-10 rounded-full"
          />
          <div v-else class="flex items-center justify-center w-10 h-10 font-bold rounded-full bg-muted text-muted-foreground">
            {{ authStore.user?.name?.charAt(0) }}
          </div>
          <div class="min-w-0 leading-tight">
            <p class="text-sm font-semibold truncate text-card-foreground">{{ authStore.user?.name }}</p>
            <p class="text-xs truncate text-muted-foreground">{{ authStore.user?.kelas || '-' }}</p>
          </div>
        </div>

        <div class="px-3 py-2 mt-3 rounded-lg bg-muted">
          <p class="text-[10px] uppercase tracking-wide text-muted-foreground">NISN</p>
          <p class="text-xs font-medium text-card-foreground">{{ authStore.user?.nisn || '-' }}</p>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="flex items-center justify-center w-full gap-2 px-3 py-2 mt-3 text-sm font-medium border rounded-lg border-border text-muted-foreground hover:border-destructive hover:text-destructive"
        >
          <LogOut class="w-4 h-4" />
          Keluar / Logout
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  BellRing,
  Boxes,
  FilePlus2,
  History,
  LayoutDashboard,
  LogOut,
  MessageSquare,
  Settings,
  User,
} from 'lucide-vue-next';
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
  { label: 'Dashboard Saya', icon: LayoutDashboard, to: '/' },
  { label: 'Riwayat Peminjaman', icon: History, to: '/peminjaman' },
  { label: 'Pengajuan Baru', icon: FilePlus2, to: '/peminjaman/ajukan' },
  { label: 'Notifikasi & Status', icon: BellRing, to: '/notifikasi', badge: notifCount.value },
  { label: 'Pengaduan', icon: MessageSquare, to: '/pengaduan' },
  { label: 'Profil Saya', icon: User, to: '/profil' },
  { label: 'Pengaturan', icon: Settings, to: '/pengaturan' },
]);

function isActive(to) {
  return route.path === to;
}

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}
</script>
