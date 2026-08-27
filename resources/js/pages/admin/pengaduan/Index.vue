<template>
  <div class="p-6">
    <h1 class="mb-4 text-xl font-bold">Kelola Pengaduan</h1>

    <div v-if="pengaduanStore.loading" class="text-gray-500">Memuat data...</div>

    <table v-else class="w-full text-sm bg-white border rounded shadow">
      <thead class="text-left bg-gray-100">
        <tr>
          <th class="p-3">Siswa</th>
          <th class="p-3">Subjek</th>
          <th class="p-3">Tanggal</th>
          <th class="p-3">Status</th>
          <th class="p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in pengaduanStore.adminItems" :key="item.id" class="border-t">
          <td class="p-3">{{ item.user.name }}</td>
          <td class="p-3">{{ item.subjek }}</td>
          <td class="p-3">{{ formatTanggal(item.created_at) }}</td>
          <td class="p-3">
            <span :class="statusClass(item.status)" class="px-2 py-1 text-xs rounded">
              {{ statusLabel(item.status) }}
            </span>
          </td>
          <td class="p-3">
            <button @click="openDetail(item)" class="text-blue-600 hover:underline">
              Lihat
            </button>
          </td>
        </tr>
        <tr v-if="pengaduanStore.adminItems.length === 0">
          <td colspan="5" class="p-3 text-center text-gray-500">Belum ada pengaduan masuk.</td>
        </tr>
      </tbody>
    </table>

    <div v-if="showDetailModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="w-full max-w-md p-6 bg-white rounded shadow">
        <h2 class="mb-1 text-lg font-bold">{{ selectedItem?.subjek }}</h2>
        <p class="mb-3 text-xs text-gray-500">
          Dari: {{ selectedItem?.user.name }} · {{ formatTanggal(selectedItem?.created_at) }}
        </p>

        <div class="p-3 mb-4 text-sm bg-gray-50 border rounded">
          {{ selectedItem?.pesan }}
        </div>

        <label class="block mb-1 text-sm font-medium">Tanggapan Admin</label>
        <textarea
          v-model="tanggapan"
          rows="4"
          placeholder="Tulis tanggapan untuk siswa..."
          class="w-full px-3 py-2 mb-4 text-sm border rounded"
          :disabled="selectedItem?.status === 'selesai'"
        ></textarea>

        <div v-if="selectedItem?.status === 'selesai'" class="mb-3 text-xs text-green-600">
          Pengaduan ini sudah ditandai selesai.
        </div>

        <div class="flex gap-2">
          <button
            v-if="selectedItem?.status !== 'selesai'"
            @click="handleSelesaikan"
            class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700"
          >
            Tandai Selesai
          </button>
          <button @click="closeDetail" class="px-4 py-2 border rounded hover:bg-gray-50">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { usePengaduanStore } from '../../../stores/pengaduan';

const pengaduanStore = usePengaduanStore();

const showDetailModal = ref(false);
const selectedItem = ref(null);
const tanggapan = ref('');

onMounted(() => {
  pengaduanStore.fetchAllForAdmin();
});

const statusMap = {
  belum_dibaca: { label: 'Belum Dibaca', class: 'bg-yellow-100 text-yellow-700' },
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

async function openDetail(item) {
  const detail = await pengaduanStore.fetchDetail(item.id);
  selectedItem.value = detail;
  tanggapan.value = detail.tanggapan_admin || '';
  showDetailModal.value = true;
}

function closeDetail() {
  showDetailModal.value = false;
  selectedItem.value = null;
  tanggapan.value = '';
}

async function handleSelesaikan() {
  await pengaduanStore.selesaikan(selectedItem.value.id, tanggapan.value);
  closeDetail();
}
</script>
