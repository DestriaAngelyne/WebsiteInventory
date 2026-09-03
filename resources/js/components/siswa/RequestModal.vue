<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="request-modal-title"
  >
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="handleClose" aria-hidden="true" />

    <div class="relative z-10 w-full max-w-lg bg-white border shadow-2xl rounded-2xl border-border">
      <div class="flex items-start justify-between gap-4 p-6 border-b border-border">
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-secondary text-primary">
            <PackageSearch class="w-5 h-5" />
          </div>
          <div>
            <h2 id="request-modal-title" class="text-base font-bold text-card-foreground">Pengajuan Pinjam Barang</h2>
            <p class="text-sm text-muted-foreground">Lengkapi formulir peminjaman sarpras.</p>
          </div>
        </div>
        <button type="button" @click="handleClose" aria-label="Tutup" class="flex items-center justify-center w-8 h-8 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form class="p-6 space-y-4" @submit.prevent="handleSubmit">
        <p v-if="errorMessage" class="text-sm text-danger-foreground">{{ errorMessage }}</p>

        <label class="block">
          <span class="block mb-1.5 text-sm font-medium text-card-foreground">Cari / Pilih Barang</span>
          <select
            v-model="form.barang_id"
            required
            class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
          >
            <option value="" disabled>Pilih barang...</option>
            <option v-for="item in barangStore.items" :key="item.id" :value="item.id">
              {{ item.nama_barang }} (Stok: {{ item.stok }})
            </option>
          </select>
        </label>

        <label class="block">
          <span class="block mb-1.5 text-sm font-medium text-card-foreground">Lokasi Pengambilan</span>
          <input
            type="text"
            :value="selectedBarang?.lokasi || '-'"
            readonly
            class="w-full px-3 py-2.5 text-sm rounded-lg cursor-not-allowed border border-border bg-muted text-muted-foreground outline-none"
          />
        </label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="block">
            <span class="block mb-1.5 text-sm font-medium text-card-foreground">Tanggal &amp; Waktu Pinjam</span>
            <input
              v-model="form.tanggalWaktuPinjam"
              type="datetime-local"
              required
              class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
            />
          </label>
          <label class="block">
            <span class="block mb-1.5 text-sm font-medium text-card-foreground">Durasi Peminjaman (Hari)</span>
            <input
              v-model.number="form.durasi"
              type="number"
              min="1"
              required
              class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
            />
          </label>
        </div>

        <label class="block">
          <span class="block mb-1.5 text-sm font-medium text-card-foreground">Alasan Peminjaman</span>
          <textarea
            v-model="form.alasan"
            rows="3"
            placeholder="Contoh: Untuk presentasi tugas PKWU"
            class="w-full resize-none rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-foreground outline-none placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20"
          ></textarea>
        </label>

        <div class="flex items-center justify-end gap-3 pt-5 border-t border-border">
          <button type="button" @click="handleClose" class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">
            Batal
          </button>
          <button type="submit" :disabled="loading" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-[#1d4ed8] disabled:opacity-50">
            {{ loading ? 'Mengirim...' : 'Kirim Pengajuan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { PackageSearch, X } from 'lucide-vue-next';
import { useBarangStore } from '../../stores/barang';
import { usePeminjamanStore } from '../../stores/peminjaman';

const props = defineProps({
  open: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'submitted']);

const barangStore = useBarangStore();
const peminjamanStore = usePeminjamanStore();

const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
  barang_id: '',
  tanggalWaktuPinjam: '',
  durasi: 1,
  alasan: '',
});

const selectedBarang = computed(() =>
  barangStore.items.find((item) => item.id === form.barang_id)
);

watch(
  () => props.open,
  (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
    if (isOpen) {
      barangStore.fetchTersedia();
      form.barang_id = '';
      form.tanggalWaktuPinjam = '';
      form.durasi = 1;
      form.alasan = '';
      errorMessage.value = '';
    }
  }
);

function handleClose() {
  emit('close');
}

function onKeydown(e) {
  if (e.key === 'Escape' && props.open) handleClose();
}
onMounted(() => document.addEventListener('keydown', onKeydown));

async function handleSubmit() {
  errorMessage.value = '';

  const tanggalPinjam = form.tanggalWaktuPinjam ? form.tanggalWaktuPinjam.slice(0, 10) : '';
  const kembali = new Date(form.tanggalWaktuPinjam);
  kembali.setDate(kembali.getDate() + Number(form.durasi || 1));
  const tanggalKembaliRencana = kembali.toISOString().slice(0, 10);

  loading.value = true;
  try {
    await peminjamanStore.ajukan({
      barang_id: form.barang_id,
      jumlah: 1,
      alasan: form.alasan,
      tanggal_pinjam: tanggalPinjam,
      tanggal_kembali_rencana: tanggalKembaliRencana,
    });
    emit('submitted');
    handleClose();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengajukan peminjaman.';
  } finally {
    loading.value = false;
  }
}
</script>
