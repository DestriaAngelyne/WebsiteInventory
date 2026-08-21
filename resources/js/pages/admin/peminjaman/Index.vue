<template>
  <div class="p-6">
    <h1 class="mb-4 text-xl font-bold">Kelola Peminjaman</h1>

    <div v-if="peminjamanStore.loading" class="text-gray-500">Memuat data...</div>

    <table v-else class="w-full text-sm bg-white border rounded shadow">
      <thead class="text-left bg-gray-100">
        <tr>
          <th class="p-3">Siswa</th>
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
        <tr v-for="item in peminjamanStore.adminItems" :key="item.id" class="border-t">
          <td class="p-3">{{ item.user.name }}</td>
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
            <div v-if="item.status_perpanjangan === 'pending'">
              <span class="block px-2 py-1 mb-1 text-xs text-yellow-700 bg-yellow-100 rounded w-fit">
                Minta s/d {{ formatTanggal(item.tanggal_kembali_diminta) }}
              </span>
              <div class="space-x-2">
                <button @click="handleApprovePerpanjangan(item.id)" class="text-xs text-green-600 hover:underline">
                  Setujui
                </button>
                <button @click="handleRejectPerpanjangan(item.id)" class="text-xs text-red-600 hover:underline">
                  Tolak
                </button>
              </div>
            </div>
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
            <template v-if="item.status === 'pending'">
              <button @click="handleApprove(item.id)" class="text-green-600 hover:underline">
                Setujui
              </button>
              <button @click="handleReject(item.id)" class="text-red-600 hover:underline">
                Tolak
              </button>
            </template>
            <button
              v-if="item.status === 'dikembalikan'"
              @click="openVerifikasiModal(item)"
              class="text-blue-600 hover:underline"
            >
              Verifikasi
            </button>
          </td>
        </tr>
        <tr v-if="peminjamanStore.adminItems.length === 0">
          <td colspan="11" class="p-3 text-center text-gray-500">Belum ada pengajuan peminjaman.</td>
        </tr>
      </tbody>
    </table>

    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-sm p-6 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Verifikasi Pengembalian</h2>
        <p class="mb-3 text-sm text-gray-600">
          Barang: <strong>{{ selectedItem?.barang.nama_barang }}</strong><br />
          Siswa: {{ selectedItem?.user.name }}
        </p>

        <div v-if="selectedItem?.catatan_pengembalian" class="p-3 mb-3 text-sm bg-gray-50 border rounded">
          <p class="mb-1 font-medium text-gray-700">Catatan dari siswa:</p>
          <p class="text-gray-600">{{ selectedItem.catatan_pengembalian }}</p>
        </div>

        <label class="block mb-1 text-sm font-medium">Kondisi Barang Saat Dikembalikan</label>
        <select v-model="kondisiTerpilih" class="w-full px-3 py-2 mb-2 border rounded">
          <option value="baik">Baik</option>
          <option value="rusak_ringan">Rusak Ringan</option>
          <option value="rusak_berat">Rusak Berat</option>
          <option value="hilang">Hilang</option>
        </select>

        <p v-if="kondisiTerpilih === 'hilang'" class="mb-4 text-xs text-red-600">
          ⚠️ Barang akan dikenakan biaya ganti sebesar harga barang, dan stok tidak akan dikembalikan.
        </p>
        <p v-else class="mb-4 text-xs text-gray-500">
          Stok barang akan dikembalikan otomatis.
        </p>

        <div class="flex gap-2">
          <button @click="handleVerifikasi" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
            Konfirmasi Verifikasi
          </button>
          <button @click="closeModal" class="px-4 py-2 border rounded hover:bg-gray-50">
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

const showModal = ref(false);
const selectedItem = ref(null);
const kondisiTerpilih = ref('baik');

onMounted(() => {
  peminjamanStore.fetchAllForAdmin();
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

async function handleApprove(id) {
  if (!confirm('Setujui peminjaman ini? Stok barang akan berkurang.')) return;
  try {
    await peminjamanStore.approve(id);
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menyetujui peminjaman.');
  }
}

async function handleReject(id) {
  if (!confirm('Tolak peminjaman ini?')) return;
  await peminjamanStore.reject(id);
}

async function handleApprovePerpanjangan(id) {
  if (!confirm('Setujui permintaan perpanjangan ini?')) return;
  await peminjamanStore.approvePerpanjangan(id);
}

async function handleRejectPerpanjangan(id) {
  if (!confirm('Tolak permintaan perpanjangan ini?')) return;
  await peminjamanStore.rejectPerpanjangan(id);
}

function openVerifikasiModal(item) {
  selectedItem.value = item;
  kondisiTerpilih.value = 'baik';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedItem.value = null;
}

async function handleVerifikasi() {
  try {
    await peminjamanStore.verifikasi(selectedItem.value.id, kondisiTerpilih.value);
    closeModal();
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal memverifikasi pengembalian.');
  }
}
</script>