<template>
  <!-- Guest -->
  <div v-if="!authStore.isLoggedIn" class="flex items-center justify-center h-screen">
    <h1 class="text-2xl font-bold text-gray-800">SarprasHub 🚀</h1>
  </div>

  <!-- Admin -->
  <div v-else-if="authStore.isAdmin" class="flex flex-col items-center justify-center gap-3 h-screen">
    <h1 class="text-2xl font-bold text-gray-800">Selamat datang, {{ authStore.user?.name }} 👋</h1>
    <router-link to="/admin/barang" class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
      Buka Kelola Barang
    </router-link>
  </div>

  <!-- Siswa -->
  <template v-else>
    <DashboardTopHeader
      :title="`Halo, ${authStore.user?.name?.split(' ')[0] || authStore.user?.name} 👋`"
      subtitle="Pantau status peminjaman sarpras kamu di sini."
    />

    <main class="flex-1 px-6 py-6 space-y-8 sm:px-8">
      <div v-if="peminjamanStore.loading" class="text-gray-500">Memuat data...</div>

      <template v-else>
        <!-- Stat cards -->
        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
          <div class="p-5 bg-white border shadow-sm rounded-xl">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-gray-500">Barang Sedang Dipinjam</p>
              <div class="flex items-center justify-center w-10 h-10 text-blue-700 rounded-lg bg-blue-50">📦</div>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-800">{{ stats.sedangDipinjam }} Unit</p>
            <p class="mt-1 text-sm text-gray-500 truncate">{{ stats.sedangDipinjamDetail }}</p>
          </div>

          <div class="p-5 bg-white border shadow-sm rounded-xl">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-gray-500">Menunggu Persetujuan</p>
              <div class="flex items-center justify-center w-10 h-10 text-yellow-700 rounded-lg bg-yellow-50">⏳</div>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-800">{{ stats.menunggu }} Pengajuan</p>
            <p class="mt-1 text-sm text-gray-500 truncate">{{ stats.menungguDetail }}</p>
          </div>

          <div class="p-5 bg-white border shadow-sm rounded-xl">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-gray-500">Total Denda / Keterlambatan</p>
              <div
                class="flex items-center justify-center w-10 h-10 rounded-lg"
                :class="stats.totalTagihan > 0 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'"
              >
                💰
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-800">Rp{{ stats.totalTagihan.toLocaleString('id-ID') }}</p>
            <p class="mt-1 text-sm" :class="stats.totalTagihan > 0 ? 'text-red-600' : 'text-green-600'">
              {{ stats.totalTagihan > 0 ? 'Ada tagihan belum lunas' : 'Status Aman' }}
            </p>
          </div>
        </section>

        <!-- Barang yang sedang dibawa -->
        <section>
          <h2 class="mb-4 text-base font-bold text-gray-800">Barang Yang Sedang Kamu Bawa</h2>

          <div v-if="sedangDipinjamList.length === 0" class="p-6 text-center text-gray-500 bg-white border rounded-xl">
            Kamu tidak sedang membawa barang apapun.
          </div>

          <div v-else class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div v-for="item in sedangDipinjamList" :key="item.id" class="flex flex-col p-5 bg-white border shadow-sm rounded-xl">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-medium text-blue-600">{{ item.barang?.kategori }}</p>
                  <h3 class="mt-0.5 text-base font-semibold text-gray-800">{{ item.barang?.nama_barang }}</h3>
                </div>
                <span :class="toneClass(getStatusInfo(item).tone)" class="px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                  {{ getStatusInfo(item).label }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="px-3 py-2.5 rounded-lg bg-gray-50">
                  <p class="text-[11px] text-gray-500">Tgl Pinjam</p>
                  <p class="mt-0.5 text-sm font-medium text-gray-800">{{ formatTanggal(item.tanggal_pinjam) }}</p>
                </div>
                <div class="px-3 py-2.5 rounded-lg bg-gray-50">
                  <p class="text-[11px] text-gray-500">Batas Kembali</p>
                  <p class="mt-0.5 text-sm font-medium text-gray-800">{{ formatTanggal(item.tanggal_kembali_rencana) }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2.5 pt-4 mt-4 border-t">
                <button
                  type="button"
                  @click="handleCetakBukti(item)"
                  class="inline-flex items-center justify-center flex-1 gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                  🧾 Cetak Bukti Pinjam
                </button>
                <button
                  type="button"
                  @click="openPerpanjanganModal(item)"
                  :disabled="item.status_perpanjangan === 'pending'"
                  class="inline-flex items-center justify-center flex-1 gap-2 px-3 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50 disabled:opacity-50"
                >
                  ⏱ {{ item.status_perpanjangan === 'pending' ? 'Menunggu Perpanjangan' : 'Minta Perpanjangan' }}
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Riwayat & status pengajuan -->
        <section class="bg-white border shadow-sm rounded-xl">
          <div class="flex flex-col gap-3 p-5 border-b sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-bold text-gray-800">Riwayat &amp; Status Pengajuan</h2>
            <input
              v-model="query"
              type="search"
              placeholder="Cari transaksi atau barang..."
              class="w-full px-3 py-2 text-sm border rounded-lg sm:w-72"
            />
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse min-w-[720px]">
              <thead>
                <tr class="border-b">
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">ID Transaksi</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">Nama Barang</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">Tanggal Pengajuan</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">Durasi</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">Status</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide text-gray-400 uppercase">Detail</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredHistory" :key="item.id" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="px-5 py-4 text-sm font-medium text-blue-600">PJM-{{ item.id }}</td>
                  <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ item.barang?.nama_barang }}</td>
                  <td class="px-5 py-4 text-sm text-gray-500">{{ formatTanggal(item.created_at) }}</td>
                  <td class="px-5 py-4 text-sm text-gray-500">{{ hitungDurasi(item) }} Hari</td>
                  <td class="px-5 py-4">
                    <span :class="toneClass(getStatusInfo(item).tone)" class="px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                      {{ getStatusInfo(item).label }}
                    </span>
                  </td>
                  <td class="px-5 py-4">
                    <button
                      type="button"
                      @click="openDetailModal(item)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium border rounded-lg hover:bg-gray-50"
                    >
                      👁 Detail
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredHistory.length === 0">
                  <td colspan="6" class="px-5 py-10 text-sm text-center text-gray-500">
                    Tidak ada transaksi yang cocok dengan pencarian.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>

    <!-- Modal: Minta Perpanjangan -->
    <div v-if="showPerpanjanganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-sm p-6 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Minta Perpanjangan</h2>
        <p class="mb-3 text-sm text-gray-600">
          Barang: <strong>{{ selectedItem?.barang?.nama_barang }}</strong><br />
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

    <!-- Modal: Detail transaksi -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-sm p-6 bg-white rounded shadow">
        <h2 class="mb-1 text-lg font-bold">PJM-{{ selectedItem?.id }}</h2>
        <p class="mb-4 text-xs text-gray-500">{{ selectedItem?.barang?.nama_barang }}</p>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-gray-500">Jumlah</span><span>{{ selectedItem?.jumlah }}</span></div>
          <div class="flex justify-between"><span class="text-gray-500">Tgl Pinjam</span><span>{{ formatTanggal(selectedItem?.tanggal_pinjam) }}</span></div>
          <div class="flex justify-between"><span class="text-gray-500">Batas Kembali</span><span>{{ formatTanggal(selectedItem?.tanggal_kembali_rencana) }}</span></div>
          <div v-if="selectedItem?.alasan" class="flex justify-between gap-3"><span class="text-gray-500 whitespace-nowrap">Alasan</span><span class="text-right">{{ selectedItem?.alasan }}</span></div>
          <div v-if="selectedItem?.kondisi_pengembalian" class="flex justify-between"><span class="text-gray-500">Kondisi Kembali</span><span class="capitalize">{{ selectedItem?.kondisi_pengembalian?.replace('_', ' ') }}</span></div>
          <div v-if="selectedItem?.denda > 0" class="flex justify-between text-red-600"><span>Denda</span><span>Rp{{ selectedItem?.denda?.toLocaleString('id-ID') }}</span></div>
          <div v-if="selectedItem?.biaya_ganti > 0" class="flex justify-between text-red-700"><span>Biaya Ganti</span><span>Rp{{ selectedItem?.biaya_ganti?.toLocaleString('id-ID') }}</span></div>
        </div>

        <button @click="showDetailModal = false" class="w-full px-4 py-2 mt-5 text-sm border rounded hover:bg-gray-50">
          Tutup
        </button>
      </div>
    </div>
  </template>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { usePeminjamanStore } from '../stores/peminjaman';
import DashboardTopHeader from '../components/siswa/DashboardTopHeader.vue';

const authStore = useAuthStore();
const peminjamanStore = usePeminjamanStore();

const query = ref('');
const showPerpanjanganModal = ref(false);
const showDetailModal = ref(false);
const selectedItem = ref(null);
const tanggalBaru = ref('');

onMounted(() => {
  if (authStore.isLoggedIn && !authStore.isAdmin) {
    peminjamanStore.fetchMyPeminjaman();
  }
});

const sedangDipinjamList = computed(() =>
  peminjamanStore.myItems.filter((item) => item.status === 'disetujui')
);

const stats = computed(() => {
  const aktif = sedangDipinjamList.value;
  const menungguList = peminjamanStore.myItems.filter((item) => item.status === 'pending');
  let totalTagihan = 0;

  for (const item of peminjamanStore.myItems) {
    totalTagihan += (item.denda || 0) + (item.biaya_ganti || 0);
  }

  return {
    sedangDipinjam: aktif.length,
    sedangDipinjamDetail: aktif.length > 0 ? aktif.map((i) => i.barang?.nama_barang).join(' & ') : 'Tidak ada barang dipinjam',
    menunggu: menungguList.length,
    menungguDetail: menungguList.length > 0 ? menungguList.map((i) => i.barang?.nama_barang).join(' & ') : 'Tidak ada pengajuan',
    totalTagihan,
  };
});

const filteredHistory = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return peminjamanStore.myItems;
  return peminjamanStore.myItems.filter((item) => {
    const info = getStatusInfo(item);
    return (
      String(item.id).includes(q) ||
      item.barang?.nama_barang?.toLowerCase().includes(q) ||
      info.label.toLowerCase().includes(q)
    );
  });
});

function getStatusInfo(item) {
  if (item.status === 'pending') return { label: 'Menunggu Persetujuan', tone: 'warning' };
  if (item.status === 'ditolak') return { label: 'Ditolak', tone: 'danger' };
  if (item.status === 'dibatalkan') return { label: 'Dibatalkan', tone: 'neutral' };
  if (item.status === 'dikembalikan') return { label: 'Menunggu Verifikasi', tone: 'warning' };
  if (item.status === 'selesai') return { label: 'Selesai', tone: 'neutral' };

  if (item.status === 'disetujui') {
    const rencana = new Date(item.tanggal_kembali_rencana);
    rencana.setHours(0, 0, 0, 0);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (today > rencana) return { label: 'Terlambat', tone: 'danger' };
    if (today.getTime() === rencana.getTime()) return { label: 'Jatuh Tempo Hari Ini', tone: 'warning' };

    const sisaHari = Math.round((rencana - today) / (1000 * 60 * 60 * 24));
    return { label: `Dipinjam · Sisa ${sisaHari} Hari`, tone: 'success' };
  }

  return { label: item.status, tone: 'neutral' };
}

function toneClass(tone) {
  const map = {
    success: 'bg-green-100 text-green-700',
    warning: 'bg-yellow-100 text-yellow-700',
    danger: 'bg-red-100 text-red-700',
    neutral: 'bg-gray-100 text-gray-700',
  };
  return map[tone] || map.neutral;
}

function hitungDurasi(item) {
  if (!item.tanggal_pinjam || !item.tanggal_kembali_rencana) return '-';
  const mulai = new Date(item.tanggal_pinjam);
  const selesai = new Date(item.tanggal_kembali_rencana);
  const hari = Math.max(1, Math.round((selesai - mulai) / (1000 * 60 * 60 * 24)));
  return hari;
}

function formatTanggal(tanggal) {
  if (!tanggal) return '-';
  return new Date(tanggal).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
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

function openDetailModal(item) {
  selectedItem.value = item;
  showDetailModal.value = true;
}

function handleCetakBukti(item) {
  const win = window.open('', '_blank', 'width=420,height=600');
  win.document.write(`
    <html>
      <head><title>Bukti Peminjaman PJM-${item.id}</title></head>
      <body style="font-family: sans-serif; padding: 24px;">
        <h2>Bukti Peminjaman Sarpras</h2>
        <p><strong>ID Transaksi:</strong> PJM-${item.id}</p>
        <p><strong>Nama Peminjam:</strong> ${authStore.user?.name || '-'}</p>
        <p><strong>Barang:</strong> ${item.barang?.nama_barang || '-'}</p>
        <p><strong>Jumlah:</strong> ${item.jumlah}</p>
        <p><strong>Tanggal Pinjam:</strong> ${formatTanggal(item.tanggal_pinjam)}</p>
        <p><strong>Batas Kembali:</strong> ${formatTanggal(item.tanggal_kembali_rencana)}</p>
        <hr />
        <p style="font-size: 12px; color: #666;">Tunjukkan bukti ini ke petugas sarpras saat mengambil barang.</p>
        <script>window.print();<\/script>
      </body>
    </html>
  `);
  win.document.close();
}
</script>
