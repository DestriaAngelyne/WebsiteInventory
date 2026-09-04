<template>
  <Transition name="modal-fade">
    <div
      v-if="uiStore.showRequestModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="request-modal-title"
    >
      <div class="absolute inset-0 bg-foreground/50 backdrop-blur-md" @click="handleClose" aria-hidden="true" />

      <Transition name="modal-pop" appear>
        <div class="relative z-10 w-full max-w-lg overflow-hidden border rounded-2xl border-border bg-card shadow-2xl shadow-black/20">
          <div class="flex items-start justify-between gap-4 p-6 border-b bg-gradient-to-b from-secondary/40 to-transparent border-border">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-primary text-primary-foreground shadow-sm shadow-primary/30">
                <PackageSearch class="w-5 h-5" />
              </div>
              <div>
                <h2 id="request-modal-title" class="text-base font-bold text-card-foreground">Pengajuan Pinjam Barang</h2>
                <p class="text-sm text-muted-foreground">Lengkapi formulir peminjaman sarpras.</p>
              </div>
            </div>
            <button
              type="button"
              @click="handleClose"
              aria-label="Tutup"
              class="flex items-center justify-center w-8 h-8 transition-colors rounded-full text-muted-foreground hover:bg-muted hover:text-foreground"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form class="p-6 space-y-5" @submit.prevent="handleSubmit">
            <Transition name="fade">
              <p v-if="errorMessage" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg text-danger-foreground bg-danger">
                <AlertCircle class="w-4 h-4 shrink-0" />
                {{ errorMessage }}
              </p>
            </Transition>

            <label class="block">
              <span class="block mb-1.5 text-sm font-medium text-card-foreground">Cari / Pilih Barang</span>
              <div class="relative">
                <Package class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none left-3 top-1/2 text-muted-foreground" />
                <select
                  v-model="form.barang_id"
                  required
                  class="w-full appearance-none rounded-lg border border-border bg-background py-2.5 pl-9 pr-9 text-sm text-foreground outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
                >
                  <option value="" disabled>Pilih barang...</option>
                  <option v-for="item in barangStore.items" :key="item.id" :value="item.id">
                    {{ item.nama_barang }} (Stok: {{ item.stok }})
                  </option>
                </select>
                <ChevronDown class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none right-3 top-1/2 text-muted-foreground" />
              </div>
            </label>

            <label class="block">
              <span class="block mb-1.5 text-sm font-medium text-card-foreground">Lokasi Pengambilan</span>
              <div class="relative">
                <MapPin class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none left-3 top-1/2 text-muted-foreground" />
                <input
                  type="text"
                  :value="selectedBarang?.lokasi || 'Pilih barang terlebih dahulu'"
                  readonly
                  class="w-full cursor-not-allowed rounded-lg border border-border bg-muted py-2.5 pl-9 pr-3 text-sm text-muted-foreground outline-none"
                />
              </div>
            </label>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <label class="block">
                <span class="block mb-1.5 text-sm font-medium text-card-foreground">Tanggal &amp; Waktu Pinjam</span>
                <div class="relative">
                  <CalendarClock class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none left-3 top-1/2 text-muted-foreground" />
                  <input
                    v-model="form.tanggalWaktuPinjam"
                    type="datetime-local"
                    required
                    class="w-full rounded-lg border border-border bg-background py-2.5 pl-9 pr-3 text-sm text-foreground outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
                  />
                </div>
              </label>
              <label class="block">
                <span class="block mb-1.5 text-sm font-medium text-card-foreground">Durasi Peminjaman (Hari)</span>
                <div class="relative">
                  <Timer class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none left-3 top-1/2 text-muted-foreground" />
                  <input
                    v-model.number="form.durasi"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-lg border border-border bg-background py-2.5 pl-9 pr-3 text-sm text-foreground outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
                  />
                </div>
              </label>
            </div>

            <label class="block">
              <span class="block mb-1.5 text-sm font-medium text-card-foreground">Alasan Peminjaman</span>
              <textarea
                v-model="form.alasan"
                rows="3"
                placeholder="Contoh: Untuk presentasi tugas PKWU"
                class="w-full resize-none rounded-lg border border-border bg-background px-3 py-2.5 text-sm text-foreground outline-none transition-all placeholder:text-muted-foreground focus:border-primary focus:ring-4 focus:ring-primary/10"
              ></textarea>
            </label>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-border">
              <button
                type="button"
                @click="handleClose"
                class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm shadow-primary/30 transition-all hover:bg-[#1d4ed8] hover:shadow-md disabled:opacity-50"
              >
                <SendHorizonal class="w-4 h-4" />
                {{ loading ? 'Mengirim...' : 'Kirim Pengajuan' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import {
  AlertCircle,
  CalendarClock,
  ChevronDown,
  MapPin,
  Package,
  PackageSearch,
  SendHorizonal,
  Timer,
  X,
} from 'lucide-vue-next';
import { useBarangStore } from '../../stores/barang';
import { usePeminjamanStore } from '../../stores/peminjaman';
import { useUiStore } from '../../stores/ui';

const barangStore = useBarangStore();
const peminjamanStore = usePeminjamanStore();
const uiStore = useUiStore();

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
  () => uiStore.showRequestModal,
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
  uiStore.closeRequestModal();
}

function onKeydown(e) {
  if (e.key === 'Escape' && uiStore.showRequestModal) handleClose();
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
    peminjamanStore.fetchMyPeminjaman();
    handleClose();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengajukan peminjaman.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-pop-enter-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-pop-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.modal-pop-enter-from,
.modal-pop-leave-to {
  opacity: 0;
  transform: translateY(8px) scale(0.98);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
