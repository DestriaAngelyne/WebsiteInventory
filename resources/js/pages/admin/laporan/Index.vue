<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900">Laporan & Rekap</h1>
      <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas peminjaman sarana & prasarana.</p>
    </div>

    <div v-if="peminjamanStore.loading" class="text-sm text-gray-500">Memuat data...</div>

    <template v-else>
      <!-- Stat cards -->
      <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
          <p class="text-xs text-gray-500">Total Transaksi</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ allItems.length }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
          <p class="text-xs text-gray-500">Denda + Biaya Ganti Terkumpul</p>
          <p class="mt-1 text-2xl font-bold text-red-600">Rp{{ totalTerkumpul.toLocaleString('id-ID') }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
          <p class="text-xs text-gray-500">Barang Hilang</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ jumlahHilang }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
          <p class="text-xs text-gray-500">Barang Rusak</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ jumlahRusak }}</p>
        </div>
      </div>

      <!-- Status breakdown -->
      <div class="flex flex-wrap gap-2 mb-6">
        <span
          v-for="s in statusBreakdown"
          :key="s.status"
          class="px-3 py-1.5 text-xs font-medium rounded-full"
          :class="s.class"
        >
          {{ s.label }}: {{ s.count }}
        </span>
      </div>

      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        <!-- Top barang -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
          <h2 class="mb-4 text-sm font-semibold text-gray-800">Barang Paling Sering Dipinjam</h2>
          <div v-if="topBarang.length === 0" class="text-sm text-gray-400">Belum ada data.</div>
          <div v-else class="space-y-3">
            <div v-for="b in topBarang" :key="b.nama">
              <div class="flex justify-between mb-1 text-xs">
                <span class="font-medium text-gray-700">{{ b.nama }}</span>
                <span class="text-gray-500">{{ b.count }}x</span>
              </div>
              <div class="w-full h-2 bg-gray-100 rounded-full">
                <div
                  class="h-2 rounded-full bg-blue-500"
                  :style="{ width: (b.count / topBarang[0].count * 100) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Peminjaman per bulan -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
          <h2 class="mb-4 text-sm font-semibold text-gray-800">Peminjaman per Bulan (6 Bulan Terakhir)</h2>
          <div v-if="perBulan.every(m => m.count === 0)" class="text-sm text-gray-400">Belum ada data.</div>
          <div v-else class="flex items-end justify-between h-40 gap-2">
            <div v-for="m in perBulan" :key="m.label" class="flex flex-col items-center flex-1 h-full">
              <div class="flex items-end flex-1 w-full">
                <div
                  class="w-full rounded-t-md bg-blue-500"
                  :style="{ height: maxPerBulan > 0 ? (m.count / maxPerBulan * 100) + '%' : '0%' }"
                  :title="`${m.label}: ${m.count} transaksi`"
                ></div>
              </div>
              <p class="mt-2 text-[10px] text-gray-500">{{ m.label }}</p>
              <p class="text-xs font-semibold text-gray-800">{{ m.count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel + filter + export -->
      <div class="bg-white border border-gray-200 rounded-xl">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
          <h2 class="text-sm font-semibold text-gray-800">Detail Transaksi</h2>

          <div class="flex flex-wrap gap-2">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari siswa atau barang..."
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <select
              v-model="statusFilter"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Status</option>
              <option value="pending">Menunggu</option>
              <option value="disetujui">Disetujui</option>
              <option value="ditolak">Ditolak</option>
              <option value="dikembalikan">Menunggu Verifikasi</option>
              <option value="selesai">Selesai</option>
              <option value="dibatalkan">Dibatalkan</option>
            </select>
            <button
              @click="exportCsv"
              class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
              Export CSV
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="text-left bg-gray-50">
              <tr>
                <th class="p-3 font-medium text-gray-500">Siswa</th>
                <th class="p-3 font-medium text-gray-500">Barang</th>
                <th class="p-3 font-medium text-gray-500">Tgl Pinjam</th>
                <th class="p-3 font-medium text-gray-500">Status</th>
                <th class="p-3 font-medium text-gray-500">Kondisi Kembali</th>
                <th class="p-3 font-medium text-gray-500">Denda</th>
                <th class="p-3 font-medium text-gray-500">Biaya Ganti</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredItems" :key="item.id" class="border-t border-gray-100">
                <td class="p-3">{{ item.user.name }}</td>
                <td class="p-3">{{ item.barang.nama_barang }}</td>
                <td class="p-3">{{ formatTanggal(item.tanggal_pinjam) }}</td>
                <td class="p-3">
                  <span :class="statusClass(item.status)" class="px-2 py-1 text-xs rounded-full">
                    {{ statusLabel(item.status) }}
                  </span>
                </td>
                <td class="p-3 capitalize">
                  {{ item.kondisi_pengembalian ? item.kondisi_pengembalian.replace('_', ' ') : '-' }}
                </td>
                <td class="p-3">
                  <span v-if="item.denda > 0" class="font-medium text-red-600">Rp{{ item.denda.toLocaleString('id-ID') }}</span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="p-3">
                  <span v-if="item.biaya_ganti > 0" class="font-bold text-red-700">Rp{{ item.biaya_ganti.toLocaleString('id-ID') }}</span>
                  <span v-else class="text-gray-400">-</span>
                </td>
              </tr>
              <tr v-if="filteredItems.length === 0">
                <td colspan="7" class="p-6 text-center text-gray-400">Tidak ada data yang cocok.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePeminjamanStore } from '../../../stores/peminjaman';

const peminjamanStore = usePeminjamanStore();

const searchQuery = ref('');
const statusFilter = ref('');

onMounted(() => {
  peminjamanStore.fetchAllForAdmin();
});

const allItems = computed(() => peminjamanStore.adminItems);

const filteredItems = computed(() => {
  return allItems.value.filter((item) => {
    const matchStatus = !statusFilter.value || item.status === statusFilter.value;
    const query = searchQuery.value.trim().toLowerCase();
    const matchSearch =
      !query ||
      item.user?.name?.toLowerCase().includes(query) ||
      item.barang?.nama_barang?.toLowerCase().includes(query);
    return matchStatus && matchSearch;
  });
});

const totalTerkumpul = computed(() =>
  allItems.value.reduce((sum, item) => sum + (item.denda || 0) + (item.biaya_ganti || 0), 0)
);

const jumlahHilang = computed(
  () => allItems.value.filter((item) => item.kondisi_pengembalian === 'hilang').length
);

const jumlahRusak = computed(
  () =>
    allItems.value.filter(
      (item) => item.kondisi_pengembalian === 'rusak_ringan' || item.kondisi_pengembalian === 'rusak_berat'
    ).length
);

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

const statusBreakdown = computed(() =>
  Object.keys(statusMap).map((status) => ({
    status,
    label: statusMap[status].label,
    class: statusMap[status].class,
    count: allItems.value.filter((item) => item.status === status).length,
  }))
);

const topBarang = computed(() => {
  const counts = {};
  for (const item of allItems.value) {
    const nama = item.barang?.nama_barang || 'Tidak diketahui';
    counts[nama] = (counts[nama] || 0) + 1;
  }
  return Object.entries(counts)
    .map(([nama, count]) => ({ nama, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 5);
});

const perBulan = computed(() => {
  const months = [];
  const now = new Date();

  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push({
      key: `${d.getFullYear()}-${d.getMonth()}`,
      label: d.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }),
      count: 0,
    });
  }

  for (const item of allItems.value) {
    if (!item.tanggal_pinjam) continue;
    const d = new Date(item.tanggal_pinjam);
    const key = `${d.getFullYear()}-${d.getMonth()}`;
    const bucket = months.find((m) => m.key === key);
    if (bucket) bucket.count++;
  }

  return months;
});

const maxPerBulan = computed(() => Math.max(...perBulan.value.map((m) => m.count), 0));

function formatTanggal(tanggal) {
  if (!tanggal) return '-';
  return new Date(tanggal).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function exportCsv() {
  const headers = ['Siswa', 'Barang', 'Jumlah', 'Tgl Pinjam', 'Rencana Kembali', 'Status', 'Kondisi Kembali', 'Denda', 'Biaya Ganti'];
  const rows = filteredItems.value.map((item) => [
    item.user.name,
    item.barang.nama_barang,
    item.jumlah,
    formatTanggal(item.tanggal_pinjam),
    formatTanggal(item.tanggal_kembali_rencana),
    statusLabel(item.status),
    item.kondisi_pengembalian ? item.kondisi_pengembalian.replace('_', ' ') : '-',
    item.denda || 0,
    item.biaya_ganti || 0,
  ]);

  const csvContent = [headers, ...rows]
    .map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    .join('\n');

  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `laporan-peminjaman-${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}
</script>
