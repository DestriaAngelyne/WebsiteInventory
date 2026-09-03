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
      @new-request="showRequestModal = true"
    />

    <main class="flex-1 px-6 py-6 space-y-8 sm:px-8">
      <div v-if="peminjamanStore.loading" class="text-gray-500">Memuat data...</div>

      <template v-else>
        <!-- Stat cards -->
        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
          <div class="p-5 border shadow-sm rounded-xl border-border bg-card">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-muted-foreground">Barang Sedang Dipinjam</p>
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-secondary text-primary">
                <PackageOpen class="w-5 h-5" />
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-card-foreground">{{ stats.sedangDipinjam }} Unit</p>
            <p class="mt-1 text-sm truncate text-muted-foreground">{{ stats.sedangDipinjamDetail }}</p>
          </div>

          <div class="p-5 border shadow-sm rounded-xl border-border bg-card">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-muted-foreground">Menunggu Persetujuan</p>
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-warning text-warning-foreground">
                <Clock4 class="w-5 h-5" />
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-card-foreground">{{ stats.menunggu }} Pengajuan</p>
            <p class="mt-1 text-sm truncate text-muted-foreground">{{ stats.menungguDetail }}</p>
          </div>

          <div class="p-5 border shadow-sm rounded-xl border-border bg-card">
            <div class="flex items-start justify-between">
              <p class="text-sm font-medium text-muted-foreground">Total Denda / Keterlambatan</p>
              <div
                class="flex items-center justify-center w-10 h-10 rounded-lg"
                :class="stats.totalTagihan > 0 ? 'bg-danger text-danger-foreground' : 'bg-success text-success-foreground'"
              >
                <Wallet class="w-5 h-5" />
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-card-foreground">Rp{{ stats.totalTagihan.toLocaleString('id-ID') }}</p>
            <p class="mt-1 text-sm" :class="stats.totalTagihan > 0 ? 'text-danger-foreground' : 'text-success-foreground'">
              {{ stats.totalTagihan > 0 ? 'Ada tagihan belum lunas' : 'Status Aman' }}
            </p>
          </div>
        </section>

        <!-- Barang yang sedang dibawa -->
        <section>
          <h2 class="mb-4 text-base font-bold text-foreground">Barang Yang Sedang Kamu Bawa</h2>

          <div v-if="sedangDipinjamList.length === 0" class="p-6 text-center border shadow-sm rounded-xl border-border bg-card text-muted-foreground">
            Kamu tidak sedang membawa barang apapun.
          </div>

          <div v-else class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div v-for="item in sedangDipinjamList" :key="item.id" class="flex flex-col p-5 border shadow-sm rounded-xl border-border bg-card">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-medium text-primary">{{ item.barang?.kategori }}</p>
                  <h3 class="mt-0.5 text-base font-semibold text-card-foreground">{{ item.barang?.nama_barang }}</h3>
                </div>
                <StatusBadge :tone="getStatusInfo(item).tone">{{ getStatusInfo(item).label }}</StatusBadge>
              </div>

              <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="px-3 py-2.5 rounded-lg bg-muted">
                  <p class="flex items-center gap-1.5 text-[11px] text-muted-foreground">
                    <CalendarClock class="w-3.5 h-3.5" />
                    Tgl Pinjam
                  </p>
                  <p class="mt-0.5 text-sm font-medium text-card-foreground">{{ formatTanggal(item.tanggal_pinjam) }}</p>
                </div>
                <div class="px-3 py-2.5 rounded-lg bg-muted">
                  <p class="flex items-center gap-1.5 text-[11px] text-muted-foreground">
                    <CalendarCheck2 class="w-3.5 h-3.5" />
                    Batas Kembali
                  </p>
                  <p class="mt-0.5 text-sm font-medium text-card-foreground">{{ formatTanggal(item.tanggal_kembali_rencana) }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2.5 pt-4 mt-4 border-t border-border">
                <button
                  type="button"
                  @click="handleCetakBukti(item)"
                  class="inline-flex items-center justify-center flex-1 gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8]"
                >
                  <QrCode class="w-4 h-4" />
                  Cetak Bukti Pinjam
                </button>
                <button
                  type="button"
                  @click="openPerpanjanganModal(item)"
                  :disabled="item.status_perpanjangan === 'pending'"
                  class="inline-flex items-center justify-center flex-1 gap-2 px-3 py-2 text-sm font-medium border rounded-lg border-border text-foreground hover:bg-muted disabled:opacity-50"
                >
                  <Timer class="w-4 h-4" />
                  {{ item.status_perpanjangan === 'pending' ? 'Menunggu Perpanjangan' : 'Minta Perpanjangan' }}
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Riwayat & status pengajuan -->
        <section class="border shadow-sm rounded-xl border-border bg-card">
          <div class="flex flex-col gap-3 p-5 border-b sm:flex-row sm:items-center sm:justify-between border-border">
            <h2 class="text-base font-bold text-card-foreground">Riwayat &amp; Status Pengajuan</h2>
            <input
              v-model="query"
              type="search"
              placeholder="Cari transaksi atau barang..."
              class="w-full px-3 py-2 text-sm border rounded-lg border-border sm:w-72 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
            />
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse min-w-[720px]">
              <thead>
                <tr class="border-b border-border">
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">ID Transaksi</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Nama Barang</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Tanggal Pengajuan</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Durasi</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Status</th>
                  <th class="px-5 py-3 text-xs font-semibold tracking-wide uppercase text-muted-foreground">Detail</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredHistory" :key="item.id" class="border-b border-border last:border-0 hover:bg-muted">
                  <td class="px-5 py-4 text-sm font-medium text-primary">PJM-{{ item.id }}</td>
                  <td class="px-5 py-4 text-sm font-medium text-card-foreground">{{ item.barang?.nama_barang }}</td>
                  <td class="px-5 py-4 text-sm text-muted-foreground">{{ formatTanggal(item.created_at) }}</td>
                  <td class="px-5 py-4 text-sm text-muted-foreground">{{ hitungDurasi(item) }} Hari</td>
                  <td class="px-5 py-4">
                    <StatusBadge :tone="getStatusInfo(item).tone">{{ getStatusInfo(item).label }}</StatusBadge>
                  </td>
                  <td class="px-5 py-4">
                    <button
                      type="button"
                      @click="openDetailModal(item)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium border rounded-lg border-border hover:bg-muted"
                    >
                      <Eye class="w-3.5 h-3.5" />
                      Detail
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredHistory.length === 0">
                  <td colspan="6" class="px-5 py-10 text-sm text-center text-muted-foreground">
                    Tidak ada transaksi yang cocok dengan pencarian.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>

    <RequestModal
      :open="showRequestModal"
      @close="showRequestModal = false"
      @submitted="peminjamanStore.fetchMyPeminjaman()"
    />

    <!-- Modal: Minta Perpanjangan -->
    <div v-if="showPerpanjanganModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
      <div class="w-full max-w-sm p-6 bg-white shadow-2xl rounded-2xl">
        <h2 class="mb-3 text-lg font-bold text-card-foreground">Minta Perpanjangan</h2>
        <p class="mb-3 text-sm text-muted-foreground">
          Barang: <strong class="text-card-foreground">{{ selectedItem?.barang?.nama_barang }}</strong><br />
          Tanggal kembali saat ini: {{ formatTanggal(selectedItem?.tanggal_kembali_rencana) }}
        </p>

        <label class="block mb-1 text-sm font-medium text-card-foreground">Tanggal Kembali Baru</label>
        <input v-model="tanggalBaru" type="date" required class="w-full px-3 py-2 mb-4 border rounded-lg border-border" />

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

    <!-- Modal: Detail transaksi -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
      <div class="w-full max-w-sm p-6 bg-white shadow-2xl rounded-2xl">
        <h2 class="mb-1 text-lg font-bold text-card-foreground">PJM-{{ selectedItem?.id }}</h2>
        <p class="mb-4 text-xs text-muted-foreground">{{ selectedItem?.barang?.nama_barang }}</p>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-muted-foreground">Jumlah</span><span>{{ selectedItem?.jumlah }}</span></div>
          <div class="flex justify-between"><span class="text-muted-foreground">Tgl Pinjam</span><span>{{ formatTanggal(selectedItem?.tanggal_pinjam) }}</span></div>
          <div class="flex justify-between"><span class="text-muted-foreground">Batas Kembali</span><span>{{ formatTanggal(selectedItem?.tanggal_kembali_rencana) }}</span></div>
          <div v-if="selectedItem?.alasan" class="flex justify-between gap-3"><span class="text-muted-foreground whitespace-nowrap">Alasan</span><span class="text-right">{{ selectedItem?.alasan }}</span></div>
          <div v-if="selectedItem?.kondisi_pengembalian" class="flex justify-between"><span class="text-muted-foreground">Kondisi Kembali</span><span class="capitalize">{{ selectedItem?.kondisi_pengembalian?.replace('_', ' ') }}</span></div>
          <div v-if="selectedItem?.denda > 0" class="flex justify-between text-danger-foreground"><span>Denda</span><span>Rp{{ selectedItem?.denda?.toLocaleString('id-ID') }}</span></div>
          <div v-if="selectedItem?.biaya_ganti > 0" class="flex justify-between text-danger-foreground"><span>Biaya Ganti</span><span>Rp{{ selectedItem?.biaya_ganti?.toLocaleString('id-ID') }}</span></div>
        </div>

        <button @click="showDetailModal = false" class="w-full px-4 py-2 mt-5 text-sm border rounded-lg border-border hover:bg-muted">
          Tutup
        </button>
      </div>
    </div>
  </template>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import {
  CalendarCheck2,
  CalendarClock,
  Clock4,
  Eye,
  PackageOpen,
  QrCode,
  Timer,
  Wallet,
} from 'lucide-vue-next';
import { useAuthStore } from '../stores/auth';
import { usePeminjamanStore } from '../stores/peminjaman';
import DashboardTopHeader from '../components/siswa/DashboardTopHeader.vue';
import RequestModal from '../components/siswa/RequestModal.vue';
import StatusBadge from '../components/ui/StatusBadge.vue';

const authStore = useAuthStore();
const peminjamanStore = usePeminjamanStore();

const query = ref('');
const showRequestModal = ref(false);
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
    sedangDipinjamDetail: aktif.map((item) => item.barang?.nama_barang).filter(Boolean).join(', ') || 'Tidak ada',
    menunggu: menungguList.length,
    menungguDetail: menungguList.map((item) => item.barang?.nama_barang).filter(Boolean).join(', ') || 'Tidak ada',
    totalTagihan,
  };
});

const filteredHistory = computed(() => {
  const q = query.value.toLowerCase().trim();
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
    return { label: 'Dipinjam', tone: 'success' };
  }

  return { label: item.status, tone: 'neutral' };
}

function hitungDurasi(item) {
  if (!item.tanggal_pinjam || !item.tanggal_kembali_rencana) return '-';
  const mulai = new Date(item.tanggal_pinjam);
  const selesai = new Date(item.tanggal_kembali_rencana);
  const diff = Math.round((selesai - mulai) / (1000 * 60 * 60 * 24));
  return diff >= 0 ? diff : '-';
}

function formatTanggal(value) {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
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
  if (!tanggalBaru.value || !selectedItem.value) return;
  await peminjamanStore.mintaPerpanjangan(selectedItem.value.id, tanggalBaru.value);
  closePerpanjanganModal();
}

function openDetailModal(item) {
  selectedItem.value = item;
  showDetailModal.value = true;
}
</script>
