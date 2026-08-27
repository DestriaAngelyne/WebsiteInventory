<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold">Pengaduan Saya</h1>
      <button
        @click="showFormModal = true"
        class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
      >
        + Buat Pengaduan
      </button>
    </div>

    <div v-if="pengaduanStore.loading" class="text-gray-500">Memuat data...</div>

    <div v-else class="space-y-3">
      <div
        v-for="item in pengaduanStore.myItems"
        :key="item.id"
        class="p-4 bg-white border rounded shadow-sm"
      >
        <div class="flex items-start justify-between">
          <div>
            <p class="font-semibold text-gray-800">{{ item.subjek }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ item.pesan }}</p>
          </div>
          <span :class="statusClass(item.status)" class="px-2 py-1 text-xs rounded whitespace-nowrap">
            {{ statusLabel(item.status) }}
          </span>
        </div>

        <div v-if="item.tanggapan_admin" class="p-3 mt-3 text-sm bg-gray-50 border rounded">
          <p class="mb-1 font-medium text-gray-700">Tanggapan Admin:</p>
          <p class="text-gray-600">{{ item.tanggapan_admin }}</p>
        </div>

        <p class="mt-2 text-xs text-gray-400">{{ formatTanggal(item.created_at) }}</p>
      </div>

      <div v-if="pengaduanStore.myItems.length === 0" class="p-6 text-center text-gray-500 bg-white border rounded">
        Belum ada pengaduan yang dibuat.
      </div>
    </div>

    <div v-if="showFormModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-md p-6 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Buat Pengaduan</h2>

        <p v-if="errorMessage" class="mb-3 text-sm text-red-600">{{ errorMessage }}</p>

        <label class="block mb-1 text-sm font-medium">Subjek</label>
        <input
          v-model="form.subjek"
          type="text"
          placeholder="Contoh: Remote proyektor rusak"
          class="w-full px-3 py-2 mb-3 border rounded"
        />

        <label class="block mb-1 text-sm font-medium">Pesan</label>
        <textarea
          v-model="form.pesan"
          rows="4"
          placeholder="Jelaskan keluhan/masalah kamu di sini..."
          class="w-full px-3 py-2 mb-4 text-sm border rounded"
        ></textarea>

        <div class="flex gap-2">
          <button @click="handleSubmit" :disabled="loading" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
            {{ loading ? 'Mengirim...' : 'Kirim Pengaduan' }}
          </button>
          <button @click="closeFormModal" class="px-4 py-2 border rounded hover:bg-gray-50">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { usePengaduanStore } from '../../../stores/pengaduan';

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
  belum_dibaca: { label: 'Menunggu', class: 'bg-yellow-100 text-yellow-700' },
  diproses: { label: 'Diproses', class: 'bg-blue-100 text-blue-700' },
  selesai: { label: 'Selesai', class: 'bg-green-100 text-green-700' },
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
