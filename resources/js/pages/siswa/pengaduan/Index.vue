<template>
  <div class="p-6 sm:p-8">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h1 class="text-lg font-bold text-foreground">Pengaduan Saya</h1>
        <p class="text-sm text-muted-foreground">Laporkan kendala terkait barang atau layanan sarpras.</p>
      </div>
      <button
        type="button"
        @click="showFormModal = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg bg-primary text-primary-foreground shadow-sm hover:bg-[#1d4ed8]"
      >
        <Plus class="w-4 h-4" />
        Buat Pengaduan
      </button>
    </div>

    <div v-if="pengaduanStore.loading" class="text-muted-foreground">Memuat data...</div>

    <div v-else class="space-y-3">
      <div
        v-for="item in pengaduanStore.myItems"
        :key="item.id"
        class="p-4 border shadow-sm rounded-xl border-border bg-card"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="font-semibold text-card-foreground">{{ item.subjek }}</p>
            <p class="mt-1 text-sm text-muted-foreground">{{ item.pesan }}</p>
          </div>
          <StatusBadge :tone="statusInfo(item.status).tone">{{ statusInfo(item.status).label }}</StatusBadge>
        </div>

        <div v-if="item.tanggapan_admin" class="p-3 mt-3 text-sm border rounded-lg border-border bg-muted">
          <p class="mb-1 font-medium text-card-foreground">Tanggapan Admin:</p>
          <p class="text-muted-foreground">{{ item.tanggapan_admin }}</p>
        </div>

        <p class="mt-2 text-xs text-muted-foreground">{{ formatTanggal(item.created_at) }}</p>
      </div>

      <div v-if="pengaduanStore.myItems.length === 0" class="p-6 text-center border shadow-sm rounded-xl border-border bg-card text-muted-foreground">
        Belum ada pengaduan yang dibuat.
      </div>
    </div>

    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl">
        <div class="flex items-start justify-between gap-4 p-6 border-b border-border">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-secondary text-primary">
              <MessageSquare class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-card-foreground">Buat Pengaduan</h2>
              <p class="text-sm text-muted-foreground">Jelaskan kendala yang kamu alami.</p>
            </div>
          </div>
          <button type="button" @click="closeFormModal" aria-label="Tutup" class="flex items-center justify-center w-8 h-8 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <p v-if="errorMessage" class="text-sm text-danger-foreground">{{ errorMessage }}</p>

          <label class="block">
            <span class="block mb-1.5 text-sm font-medium text-card-foreground">Subjek</span>
            <input
              v-model="form.subjek"
              type="text"
              placeholder="Contoh: Remote proyektor rusak"
              class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
            />
          </label>

          <label class="block">
            <span class="block mb-1.5 text-sm font-medium text-card-foreground">Pesan</span>
            <textarea
              v-model="form.pesan"
              rows="4"
              placeholder="Jelaskan keluhan/masalah kamu di sini..."
              class="w-full resize-none rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20"
            ></textarea>
          </label>

          <div class="flex items-center justify-end gap-3 pt-2 border-t border-border">
            <button @click="closeFormModal" class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">
              Batal
            </button>
            <button @click="handleSubmit" :disabled="loading" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-[#1d4ed8] disabled:opacity-50">
              {{ loading ? 'Mengirim...' : 'Kirim Pengaduan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { MessageSquare, Plus, X } from 'lucide-vue-next';
import { usePengaduanStore } from '../../../stores/pengaduan';
import StatusBadge from '../../../components/ui/StatusBadge.vue';

const pengaduanStore = usePengaduanStore();

const showFormModal = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
  subjek: '',
  pesan: '',
});

onMounted(() => {
  pengaduanStore.fetchMyPengaduan();
});

const statusMap = {
  belum_dibaca: { label: 'Menunggu', tone: 'warning' },
  diproses: { label: 'Diproses', tone: 'neutral' },
  selesai: { label: 'Selesai', tone: 'success' },
};

function statusInfo(status) {
  return statusMap[status] || { label: status, tone: 'neutral' };
}

function formatTanggal(tanggal) {
  if (!tanggal) return '-';
  return new Date(tanggal).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function closeFormModal() {
  showFormModal.value = false;
  form.subjek = '';
  form.pesan = '';
  errorMessage.value = '';
}

async function handleSubmit() {
  if (!form.subjek || !form.pesan) {
    errorMessage.value = 'Subjek dan pesan wajib diisi.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    await pengaduanStore.buat({ subjek: form.subjek, pesan: form.pesan });
    closeFormModal();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengirim pengaduan.';
  } finally {
    loading.value = false;
  }
}
</script>
