<template>
  <div class="max-w-lg p-6 mx-auto">
    <h1 class="mb-4 text-xl font-bold">Ajukan Peminjaman</h1>

    <form @submit.prevent="handleSubmit" class="p-6 bg-white rounded shadow">
      <p v-if="errorMessage" class="mb-3 text-sm text-red-600">{{ errorMessage }}</p>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Pilih Barang</label>
        <select v-model="form.barang_id" required class="w-full px-3 py-2 border rounded">
          <option value="" disabled>-- Pilih Barang --</option>
          <option v-for="item in barangStore.items" :key="item.id" :value="item.id">
            {{ item.nama_barang }} (Stok: {{ item.stok }})
          </option>
        </select>
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Jumlah</label>
        <input v-model.number="form.jumlah" type="number" min="1" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Tanggal Pinjam</label>
        <input v-model="form.tanggal_pinjam" type="date" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 text-sm font-medium">Rencana Tanggal Kembali</label>
        <input v-model="form.tanggal_kembali_rencana" type="date" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="flex gap-2">
        <button type="submit" :disabled="loading" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
          {{ loading ? 'Mengajukan...' : 'Ajukan' }}
        </button>
        <router-link to="/peminjaman" class="px-4 py-2 border rounded hover:bg-gray-50">
          Batal
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePeminjamanStore } from '../../../stores/peminjaman';
import { useBarangStore } from '../../../stores/barang';

const router = useRouter();
const peminjamanStore = usePeminjamanStore();
const barangStore = useBarangStore();

const loading = ref(false);
const errorMessage = ref('');

const form = reactive({
  barang_id: '',
  jumlah: 1,
  tanggal_pinjam: '',
  tanggal_kembali_rencana: '',
});

onMounted(() => {
  barangStore.fetchTersedia();
});

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await peminjamanStore.ajukan(form);
    router.push('/peminjaman');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengajukan peminjaman.';
  } finally {
    loading.value = false;
  }
}
</script>
