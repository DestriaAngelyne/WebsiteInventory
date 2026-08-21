<template>
  <div class="p-6">
    <h1 class="mb-4 text-xl font-bold">Notifikasi & Status</h1>

    <div v-if="peminjamanStore.loading" class="text-gray-500">Memuat data...</div>

    <div v-else class="space-y-3">
      <div
        v-for="(notif, index) in notifikasiList"
        :key="index"
        class="flex items-start justify-between p-4 bg-white border rounded shadow-sm"
      >
        <div class="flex items-start gap-3">
          <div :class="notif.iconClass" class="flex items-center justify-center w-8 h-8 text-sm font-bold rounded-full">
            {{ notif.iconLabel }}
          </div>
          <div>
            <p class="font-semibold text-gray-800">{{ notif.judul }}</p>
            <p class="text-sm text-gray-600">{{ notif.pesan }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ formatWaktu(notif.waktu) }}</p>
          </div>
        </div>
        <span :class="notif.badgeClass" class="px-2 py-1 text-xs rounded whitespace-nowrap">
          {{ notif.badgeLabel }}
        </span>
      </div>

      <div v-if="notifikasiList.length === 0" class="p-6 text-center text-gray-500 bg-white border rounded">
        Tidak ada notifikasi saat ini.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { usePeminjamanStore } from '../../../stores/peminjaman';

const peminjamanStore = usePeminjamanStore();

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
        iconLabel: '⏳',
        iconClass: 'bg-blue-100 text-blue-700',
        badgeLabel: 'Pending',
        badgeClass: 'bg-blue-100 text-blue-700',
      });
    }

    if (item.status === 'ditolak') {
      notifs.push({
        judul: 'Pengajuan ditolak',
        pesan: `Pengajuan peminjaman ${namaBarang} ditolak admin.`,
        waktu: item.updated_at,
        iconLabel: '✕',
        iconClass: 'bg-red-100 text-red-700',
        badgeLabel: 'Ditolak',
        badgeClass: 'bg-red-100 text-red-700',
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
          iconLabel: '⚠',
          iconClass: 'bg-red-100 text-red-700',
          badgeLabel: 'Terlambat',
          badgeClass: 'bg-red-100 text-red-700',
        });
      } else if (today.getTime() === rencana.getTime()) {
        notifs.push({
          judul: 'Barang jatuh tempo hari ini',
          pesan: `${namaBarang} harus dikembalikan hari ini.`,
          waktu: item.tanggal_kembali_rencana,
          iconLabel: '⏰',
          iconClass: 'bg-yellow-100 text-yellow-700',
          badgeLabel: 'Perlu tindakan',
          badgeClass: 'bg-yellow-100 text-yellow-700',
        });
      } else {
        notifs.push({
          judul: 'Pengajuan disetujui',
          pesan: `Peminjaman ${namaBarang} sudah disetujui admin.`,
          waktu: item.updated_at,
          iconLabel: '✓',
          iconClass: 'bg-green-100 text-green-700',
          badgeLabel: 'Disetujui',
          badgeClass: 'bg-green-100 text-green-700',
        });
      }
    }

    if (item.status_perpanjangan === 'disetujui') {
      notifs.push({
        judul: 'Perpanjangan disetujui',
        pesan: `Permintaan perpanjangan untuk ${namaBarang} disetujui admin.`,
        waktu: item.updated_at,
        iconLabel: '✓',
        iconClass: 'bg-green-100 text-green-700',
        badgeLabel: 'Disetujui',
        badgeClass: 'bg-green-100 text-green-700',
      });
    }

    if (item.status_perpanjangan === 'ditolak') {
      notifs.push({
        judul: 'Perpanjangan ditolak',
        pesan: `Permintaan perpanjangan untuk ${namaBarang} ditolak admin.`,
        waktu: item.updated_at,
        iconLabel: '✕',
        iconClass: 'bg-red-100 text-red-700',
        badgeLabel: 'Ditolak',
        badgeClass: 'bg-red-100 text-red-700',
      });
    }

    if (item.status === 'selesai' && (item.denda > 0 || item.biaya_ganti > 0)) {
      const total = (item.denda || 0) + (item.biaya_ganti || 0);
      notifs.push({
        judul: 'Ada tagihan yang perlu dibayar',
        pesan: `Transaksi ${namaBarang} selesai dengan total tagihan Rp${total.toLocaleString('id-ID')}.`,
        waktu: item.updated_at,
        iconLabel: 'Rp',
        iconClass: 'bg-red-100 text-red-700',
        badgeLabel: 'Perlu tindakan',
        badgeClass: 'bg-red-100 text-red-700',
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
