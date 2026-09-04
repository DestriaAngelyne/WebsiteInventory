<template>
  <div class="p-6 sm:p-8">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h1 class="text-lg font-bold text-foreground">Riwayat Peminjaman</h1>
        <p class="text-sm text-muted-foreground">Semua pengajuan peminjaman sarpras kamu.</p>
      </div>
      <button
        type="button"
        @click="uiStore.openRequestModal()"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg bg-primary text-primary-foreground shadow-sm hover:bg-[#1d4ed8]"
      >
        <Plus class="w-4 h-4" />
        Ajukan Peminjaman
      </button>
    </div>

    <div v-if="peminjamanStore.loading" class="text-muted-foreground">Memuat data...</div>

    <div v-else class="overflow-x-auto border shadow-sm rounded-xl border-border bg-card">
      <table class="w-full text-sm text-left border-collapse min-w-[900px]">
        <thead>
          <tr class="border-b border-border">
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Barang</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Jumlah</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Tgl Pinjam</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Rencana Kembali</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Status</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Perpanjangan</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Kondisi Kembali</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Denda</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Biaya Ganti</th>
            <th class="px-4 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in peminjamanStore.myItems" :key="item.id" class="border-b border-border last:border-0 hover:bg-muted/50">
            <td class="px-4 py-3 font-medium text-card-foreground">{{ item.barang.nama_barang }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ item.jumlah }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ formatTanggal(item.tanggal_pinjam) }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ formatTanggal(item.tanggal_kembali_rencana) }}</td>
            <td class="px-4 py-3">
              <StatusBadge :tone="statusInfo(item.status).tone">{{ statusInfo(item.status).label }}</StatusBadge>
            </td>
            <td class="px-4 py-3">
              <StatusBadge v-if="item.status_perpanjangan" :tone="perpanjanganInfo(item.status_perpanjangan).tone">
                {{ perpanjanganInfo(item.status_perpanjangan).label }}
              </StatusBadge>
              <span v-else class="text-muted-foreground">-</span>
            </td>
            <td class="px-4 py-3 capitalize text-muted-foreground">
              {{ item.kondisi_pengembalian ? item.kondisi_pengembalian.replace('_', ' ') : '-' }}
            </td>
            <td class="px-4 py-3">
              <span v-if="item.denda > 0" class="font-medium text-danger-foreground">
                Rp{{ item.denda.toLocaleString('id-ID') }}
              </span>
              <span v-else class="text-muted-foreground">-</span>
            </td>
            <td class="px-4 py-3">
              <span v-if="item.biaya_ganti > 0" class="font-bold text-danger-foreground">
                Rp{{ item.biaya_ganti.toLocaleString('id-ID') }}
              </span>
              <span v-else class="text-muted-foreground">-</span>
            </td>
            <td class="px-4 py-3 space-x-3 whitespace-nowrap">
              <button
                v-if="item.status === 'disetujui'"
                @click="openKembalikanModal(item)"
                class="text-sm font-medium text-primary hover:underline"
              >
                Kembalikan
              </button>
              <button
                v-if="item.status === 'disetujui' && item.status_perpanjangan !== 'pending'"
                @click="openPerpanjanganModal(item)"
                class="text-sm font-medium text-primary hover:underline"
              >
                Perpanjang
              </button>
              <button
                v-if="item.status === 'pending'"
                @click="handleBatalkan(item.id)"
                class="text-sm font-medium text-danger-foreground hover:underline"
              >
                Batalkan
              </button>
            </td>
          </tr>
          <tr v-if="peminjamanStore.myItems.length === 0">
            <td colspan="10" class="px-4 py-10 text-center text-muted-foreground">Belum ada riwayat peminjaman.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showKembalikanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-foreground/40 backdrop-blur-sm">
      <div class="w-full max-w-sm p-6 border rounded-2xl border-border bg-card shadow-2xl">
        <h2 class="mb-3 text-lg font-bold text-card-foreground">Kembalikan Barang</h2>
        <p class="mb-3 text-sm text-muted-foreground">
          Barang: <strong class="text-card-foreground">{{ selectedItem?.barang.nama_barang }}</strong>
        </p>

        <label class="block mb-1 text-sm font-medium text-card-foreground">Catatan (opsional)</label>
        <textarea
          v-model="catatanPengembalian"
          rows="3"
          placeholder="Contoh: barangnya kemarin sempat jatuh, ada goresan kecil"
          class="w-full px-3 py-2 mb-4 text-sm border rounded-lg border-border bg-background"
        ></textarea>

        <div class="flex gap-2">
          <button @click="handleKembalikan" class="px-4 py-2 text-sm font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8]">
            Konfirmasi Kembalikan
          </button>
          <button @click="closeKembalikanModal" class="px-4 py-2 text-sm border rounded-lg border-border hover:bg-muted">
            Batal
          </button>
        </div>
      </div>
    </div>

    <div v-if="showPerpanjanganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-foreground/40 backdrop-blur-sm">
      <div class="w-full max-w-sm p-6 border rounded-2xl border-border bg-card shadow-2xl">
        <h2 class="mb-3 text-lg font-bold text-card-foreground">Minta Perpanjangan</h2>
        <p class="mb-3 text-sm text-muted-foreground">
          Barang: <strong class="text-card-foreground">{{ selectedItem?.barang.nama_barang }}</strong><br />
          Tanggal kembali saat ini: {{ formatTanggal(selectedItem?.tanggal_kembali_rencana) }}
        </p>

        <label class="block mb-1 text-sm font-medium text-card-foreground">Tanggal Kembali Baru</label>
        <input v-model="tanggalBaru" type="date" required class="w-full px-3 py-2 mb-4 border rounded-lg border-border bg-background" />

        <div class="flex gap-2">
          <button @click="handleMintaPerpanjangan" class="px-4 py-2 text-sm font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8]">
            Kirim Permintaan
          </button>
          <button @click="closePerpanjanganModal" class="px-4 py-2 text-sm border rounded-lg border-border hover:bg-muted">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import { usePeminjamanStore } from '../../../stores/peminjaman';
import { useUiStore } from '../../../stores/ui';
import StatusBadge from '../../../components/ui/StatusBadge.vue';

const peminjamanStore = usePeminjamanStore();
const uiStore = useUiStore();

const showKembalikanModal = ref(false);
const showPerpanjanganModal = ref(false);
const selectedItem = ref(null);
const tanggalBaru = ref('');
const catatanPengembalian = ref('');

onMounted(() => {
  peminjamanStore.fetchMyPeminjaman();
});

const statusMap = {
  pending: { label: 'Menunggu', tone: 'warning' },
  disetujui: { label: 'Disetujui', tone: 'success' },
  ditolak: { label: 'Ditolak', tone: 'danger' },
  dikembalikan: { label: 'Menunggu Verifikasi', tone: 'warning' },
  selesai: { label: 'Selesai', tone: 'neutral' },
  dibatalkan: { label: 'Dibatalkan', tone: 'neutral' },
};

const perpanjanganMap = {
  pending: { label: 'Menunggu', tone: 'warning' },
  disetujui: { label: 'Disetujui', tone: 'success' },
  ditolak: { label: 'Ditolak', tone: 'danger' },
};

function statusInfo(status) {
  return statusMap[status] || { label: status, tone: 'neutral' };
}

function perpanjanganInfo(status) {
  return perpanjanganMap[status] || { label: status, tone: 'neutral' };
}

function formatTanggal(tanggal) {
  if (!tanggal) return '-';
  return new Date(tanggal).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function openKembalikanModal(item) {
  selectedItem.value = item;
  catatanPengembalian.value = '';
  showKembalikanModal.value = true;
}

function closeKembalikanModal() {
  showKembalikanModal.value = false;
  selectedItem.value = null;
}

async function handleKembalikan() {
  await peminjamanStore.kembalikan(selectedItem.value.id, catatanPengembalian.value);
  closeKembalikanModal();
}

async function handleBatalkan(id) {
  if (!confirm('Yakin ingin membatalkan pengajuan ini?')) return;
  await peminjamanStore.batalkan(id);
}

function openPerpanjanganModal(item) {
  selectedItem.value = item;
  tanggalBaru.value = '';
  showPerpanjanganModal.value = true;
}

function closePerpanjanganModal() {
  showPerpanjanganModal.value = false;
  selectedItem.value = null;
}

async function handleMintaPerpanjangan() {
  if (!tanggalBaru.value) {
    alert('Pilih tanggal kembali baru terlebih dahulu.');
    return;
  }

  try {
    await peminjamanStore.mintaPerpanjangan(selectedItem.value.id, tanggalBaru.value);
    closePerpanjanganModal();
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal mengajukan perpanjangan.');
  }
}
</script>
