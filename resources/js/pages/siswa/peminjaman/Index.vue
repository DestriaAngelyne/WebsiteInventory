<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold">Riwayat Peminjaman</h1>
      <router-link
        to="/peminjaman/ajukan"
        class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
      >
        + Ajukan Peminjaman
      </router-link>
    </div>

    <div v-if="peminjamanStore.loading" class="text-gray-500">Memuat data...</div>

    <table v-else class="w-full text-sm bg-white border rounded shadow">
      <thead class="text-left bg-gray-100">
        <tr>
          <th class="p-3">Barang</th>
          <th class="p-3">Jumlah</th>
          <th class="p-3">Tgl Pinjam</th>
          <th class="p-3">Rencana Kembali</th>
          <th class="p-3">Status</th>
          <th class="p-3">Perpanjangan</th>
          <th class="p-3">Kondisi Kembali</th>
          <th class="p-3">Denda</th>
          <th class="p-3">Biaya Ganti</th>
          <th class="p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in peminjamanStore.myItems" :key="item.id" class="border-t">
          <td class="p-3">{{ item.barang.nama_barang }}</td>
          <td class="p-3">{{ item.jumlah }}</td>
          <td class="p-3">{{ formatTanggal(item.tanggal_pinjam) }}</td>
          <td class="p-3">{{ formatTanggal(item.tanggal_kembali_rencana) }}</td>
          <td class="p-3">
            <span :class="statusClass(item.status)" class="px-2 py-1 text-xs rounded">
              {{ statusLabel(item.status) }}
            </span>
          </td>
          <td class="p-3">
            <span v-if="item.status_perpanjangan === 'pending'" class="px-2 py-1 text-xs text-yellow-700 bg-yellow-100 rounded">
              Menunggu
            </span>
            <span v-else-if="item.status_perpanjangan === 'disetujui'" class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded">
              Disetujui
            </span>
            <span v-else-if="item.status_perpanjangan === 'ditolak'" class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded">
              Ditolak
            </span>
            <span v-else class="text-gray-400">-</span>
          </td>
          <td class="p-3 capitalize">
            {{ item.kondisi_pengembalian ? item.kondisi_pengembalian.replace('_', ' ') : '-' }}
          </td>
          <td class="p-3">
            <span v-if="item.denda > 0" class="text-red-600 font-medium">
              Rp{{ item.denda.toLocaleString('id-ID') }}
            </span>
            <span v-else class="text-gray-400">-</span>
          </td>
          <td class="p-3">
            <span v-if="item.biaya_ganti > 0" class="text-red-700 font-bold">
              Rp{{ item.biaya_ganti.toLocaleString('id-ID') }}
            </span>
            <span v-else class="text-gray-400">-</span>
          </td>
          <td class="p-3 space-x-2">
            <button
              v-if="item.status === 'disetujui'"
              @click="openKembalikanModal(item)"
              class="text-blue-600 hover:underline"
            >
              Kembalikan
            </button>
            <button
              v-if="item.status === 'disetujui' && item.status_perpanjangan !== 'pending'"
              @click="openPerpanjanganModal(item)"
              class="text-purple-600 hover:underline"
            >
              Perpanjang
            </button>
            <button
              v-if="item.status === 'pending'"
              @click="handleBatalkan(item.id)"
              class="text-red-600 hover:underline"
            >
              Batalkan
            </button>
          </td>
        </tr>
        <tr v-if="peminjamanStore.myItems.length === 0">
          <td colspan="10" class="p-3 text-center text-gray-500">Belum ada riwayat peminjaman.</td>
        </tr>
      </tbody>
    </table>

    <div v-if="showKembalikanModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-sm p-6 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Kembalikan Barang</h2>
        <p class="mb-3 text-sm text-gray-600">
          Barang: <strong>{{ selectedItem?.barang.nama_barang }}</strong>
        </p>

        <label class="block mb-1 text-sm font-medium">Catatan (opsional)</label>
        <textarea
          v-model="catatanPengembalian"
          rows="3"
          placeholder="Contoh: barangnya kemarin sempat jatuh, ada goresan kecil"
          class="w-full px-3 py-2 mb-4 text-sm border rounded"
        ></textarea>

        <div class="flex gap-2">
          <button @click="handleKembalikan" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
            Konfirmasi Kembalikan
          </button>
          <button @click="closeKembalikanModal" class="px-4 py-2 border rounded hover:bg-gray-50">
            Batal
          </button>
        </div>
      </div>
    </div>

    <div v-if="showPerpanjanganModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-sm p-6 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Minta Perpanjangan</h2>
        <p class="mb-3 text-sm text-gray-600">
          Barang: <strong>{{ selectedItem?.barang.nama_barang }}</strong><br />
          Tanggal kembali saat ini: {{ formatTanggal(selectedItem?.tanggal_kembali_rencana) }}
        </p>

        <label class="block mb-1 text-sm font-medium">Tanggal Kembali Baru</label>
        <input v-model="tanggalBaru" type="date" required class="w-full px-3 py-2 mb-4 border rounded" />

        <div class="flex gap-2">
          <button @click="handleMintaPerpanjangan" class="px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-700">
            Kirim Permintaan
          </button>
          <button @click="closePerpanjanganModal" class="px-4 py-2 border rounded hover:bg-gray-50">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { usePeminjamanStore } from '../../../stores/peminjaman';

const peminjamanStore = usePeminjamanStore();

const showKembalikanModal = ref(false);
const showPerpanjanganModal = ref(false);
const selectedItem = ref(null);
const tanggalBaru = ref('');
const catatanPengembalian = ref('');

onMounted(() => {
  peminjamanStore.fetchMyPeminjaman();
});

const statusMap = {
  pending: { label: 'Menunggu', class: 'bg-yellow-100 text-yellow-700' },
  disetujui: { label: 'Disetujui', class: 'bg-green-100 text-green-700' },
  ditolak: { label: 'Ditolak', class: 'bg-red-100 text-red-700' },
  dikembalikan: { label: 'Menunggu Verifikasi', class: 'bg-blue-100 text-blue-700' },
  selesai: { label: 'Selesai', class: 'bg-gray-100 text-gray-700' },
  dibatalkan: { label: 'Dibatalkan', class: 'bg-gray-100 text-gray-700' },
};

function statusLabel(status) {
  return statusMap[status]?.label || status;
}

function statusClass(status) {
  return statusMap[status]?.class || '';
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
