<template>
  <div class="max-w-lg p-6 mx-auto">
    <h1 class="mb-4 text-xl font-bold">{{ isEdit ? 'Edit Barang' : 'Tambah Barang' }}</h1>

    <form @submit.prevent="handleSubmit" class="p-6 bg-white rounded shadow">
      <p v-if="errorMessage" class="mb-3 text-sm text-red-600">{{ errorMessage }}</p>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Nama Barang</label>
        <input v-model="form.nama_barang" type="text" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Kategori</label>
        <input v-model="form.kategori" type="text" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Stok</label>
        <input v-model.number="form.stok" type="number" min="0" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Kondisi</label>
        <select v-model="form.kondisi" required class="w-full px-3 py-2 border rounded">
          <option value="baik">Baik</option>
          <option value="rusak_ringan">Rusak Ringan</option>
          <option value="rusak_berat">Rusak Berat</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Lokasi</label>
        <input v-model="form.lokasi" type="text" class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-3">
        <label class="block mb-1 text-sm font-medium">Harga (untuk biaya ganti jika hilang)</label>
        <input v-model.number="form.harga" type="number" min="0" class="w-full px-3 py-2 border rounded" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 text-sm font-medium">Gambar</label>

        <div v-if="previewUrl" class="mb-2">
          <img :src="previewUrl" alt="Preview gambar" class="object-cover w-32 h-32 border rounded" />
          <p class="mt-1 text-xs text-gray-500">
            {{ file ? 'Preview gambar baru' : 'Gambar saat ini' }}
          </p>
        </div>

        <input @change="handleFileChange" type="file" accept="image/*" class="w-full" />
      </div>

      <div class="flex gap-2">
        <button type="submit" :disabled="loading" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
          {{ loading ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <router-link to="/admin/barang" class="px-4 py-2 border rounded hover:bg-gray-50">
          Batal
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBarangStore } from '../../../stores/barang';
import api from '../../../services/api';

const route = useRoute();
const router = useRouter();
const barangStore = useBarangStore();

const isEdit = !!route.params.id;
const loading = ref(false);
const errorMessage = ref('');
const file = ref(null);
const previewUrl = ref(null);

const form = reactive({
  nama_barang: '',
  kategori: '',
  stok: 0,
  kondisi: 'baik',
  lokasi: '',
  harga: 0,
});

onMounted(async () => {
  if (isEdit) {
    const response = await api.get(`/barang/${route.params.id}`);
    form.nama_barang = response.data.nama_barang;
    form.kategori = response.data.kategori;
    form.stok = response.data.stok;
    form.kondisi = response.data.kondisi;
    form.lokasi = response.data.lokasi;
    form.harga = response.data.harga;

    if (response.data.gambar) {
      previewUrl.value = `/storage/${response.data.gambar}`;
    }
  }
});

function handleFileChange(event) {
  const selected = event.target.files[0];
  file.value = selected;

  if (selected) {
    previewUrl.value = URL.createObjectURL(selected);
  }
}

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('nama_barang', form.nama_barang);
  formData.append('kategori', form.kategori);
  formData.append('stok', form.stok);
  formData.append('kondisi', form.kondisi);
  formData.append('lokasi', form.lokasi || '');
  formData.append('harga', form.harga || 0);
  if (file.value) formData.append('gambar', file.value);

  try {
    if (isEdit) {
      await barangStore.update(route.params.id, formData);
    } else {
      await barangStore.create(formData);
    }
    router.push('/admin/barang');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan data.';
  } finally {
    loading.value = false;
  }
}
</script>
