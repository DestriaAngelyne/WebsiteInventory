<template>
  <div class="p-6 sm:p-8">
    <h1 class="mb-1 text-lg font-bold text-foreground">Notifikasi & Status</h1>
    <p class="mb-5 text-sm text-muted-foreground">Update terbaru seputar peminjaman sarpras kamu.</p>

    <div v-if="peminjamanStore.loading" class="text-muted-foreground">Memuat data...</div>

    <div v-else class="space-y-3">
      <div
        v-for="(notif, index) in notifikasiList"
        :key="index"
        class="flex items-start justify-between p-4 border shadow-sm rounded-xl border-border bg-card"
      >
        <div class="flex items-start gap-3">
          <div class="flex items-center justify-center w-9 h-9 rounded-full" :class="toneIconClasses[notif.tone]">
            <component :is="notif.icon" class="w-4 h-4" />
          </div>
          <div>
            <p class="font-semibold text-card-foreground">{{ notif.judul }}</p>
            <p class="text-sm text-muted-foreground">{{ notif.pesan }}</p>
            <p class="mt-1 text-xs text-muted-foreground">{{ formatWaktu(notif.waktu) }}</p>
          </div>
        </div>
        <StatusBadge :tone="notif.tone">{{ notif.badgeLabel }}</StatusBadge>
      </div>

      <div v-if="notifikasiList.length === 0" class="p-6 text-center border shadow-sm rounded-xl border-border bg-card text-muted-foreground">
        Tidak ada notifikasi saat ini.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { AlertTriangle, Check, Clock, Clock4, Wallet, X } from 'lucide-vue-next';
import { usePeminjamanStore } from '../../../stores/peminjaman';
import StatusBadge from '../../../components/ui/StatusBadge.vue';

const peminjamanStore = usePeminjamanStore();

const toneIconClasses = {
  success: 'bg-success text-success-foreground',
  warning: 'bg-warning text-warning-foreground',
  danger: 'bg-danger text-danger-foreground',
  neutral: 'bg-neutral text-neutral-foreground',
};

onMounted(() => {
  peminjamanStore.fetchMyPeminjaman();
});

const notifikasiList = computed(() => {
  const notifs = [];

  for (const item of peminjamanStore.myItems) {
    const namaBarang = item.barang?.nama_barang || 'Barang';

    if (item.status === 'pending') {
      notifs.push({
        judul: 'Pengajuan sedang diproses',
        pesan: `Pengajuan peminjaman ${namaBarang} sedang menunggu persetujuan admin.`,
        waktu: item.created_at,
        icon: Clock4,
        tone: 'warning',
        badgeLabel: 'Pending',
      });
    }

    if (item.status === 'ditolak') {
      notifs.push({
        judul: 'Pengajuan ditolak',
        pesan: `Pengajuan peminjaman ${namaBarang} ditolak admin.`,
        waktu: item.updated_at,
        icon: X,
        tone: 'danger',
        badgeLabel: 'Ditolak',
      });
    }

    if (item.status === 'disetujui') {
      const rencana = new Date(item.tanggal_kembali_rencana);
      const today = new Date();
      rencana.setHours(0, 0, 0, 0);
      today.setHours(0, 0, 0, 0);

      if (today > rencana) {
        notifs.push({
          judul: 'Sudah lewat jatuh tempo!',
          pesan: `${namaBarang} harusnya sudah dikembalikan. Segera kembalikan untuk menghindari denda bertambah.`,
          waktu: item.tanggal_kembali_rencana,
          icon: AlertTriangle,
          tone: 'danger',
          badgeLabel: 'Terlambat',
        });
      } else if (today.getTime() === rencana.getTime()) {
        notifs.push({
          judul: 'Barang jatuh tempo hari ini',
          pesan: `${namaBarang} harus dikembalikan hari ini.`,
          waktu: item.tanggal_kembali_rencana,
          icon: Clock,
          tone: 'warning',
          badgeLabel: 'Perlu tindakan',
        });
      } else {
        notifs.push({
          judul: 'Pengajuan disetujui',
          pesan: `Peminjaman ${namaBarang} sudah disetujui admin.`,
          waktu: item.updated_at,
          icon: Check,
          tone: 'success',
          badgeLabel: 'Disetujui',
        });
      }
    }

    if (item.status_perpanjangan === 'disetujui') {
      notifs.push({
        judul: 'Perpanjangan disetujui',
        pesan: `Permintaan perpanjangan untuk ${namaBarang} disetujui admin.`,
        waktu: item.updated_at,
        icon: Check,
        tone: 'success',
        badgeLabel: 'Disetujui',
      });
    }

    if (item.status_perpanjangan === 'ditolak') {
      notifs.push({
        judul: 'Perpanjangan ditolak',
        pesan: `Permintaan perpanjangan untuk ${namaBarang} ditolak admin.`,
        waktu: item.updated_at,
        icon: X,
        tone: 'danger',
        badgeLabel: 'Ditolak',
      });
    }

    if (item.status === 'selesai' && (item.denda > 0 || item.biaya_ganti > 0)) {
      const total = (item.denda || 0) + (item.biaya_ganti || 0);
      notifs.push({
        judul: 'Ada tagihan yang perlu dibayar',
        pesan: `Transaksi ${namaBarang} selesai dengan total tagihan Rp${total.toLocaleString('id-ID')}.`,
        waktu: item.updated_at,
        icon: Wallet,
        tone: 'danger',
        badgeLabel: 'Perlu tindakan',
      });
    }
  }

  return notifs.sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
});

function formatWaktu(waktu) {
  if (!waktu) return '-';
  return new Date(waktu).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
