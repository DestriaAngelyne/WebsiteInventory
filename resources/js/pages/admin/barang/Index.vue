<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold">Data Barang</h1>
      <router-link
        to="/admin/barang/tambah"
        class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
      >
        + Tambah Barang
      </router-link>
    </div>

    <div v-if="barangStore.loading" class="text-gray-500">Memuat data...</div>

    <table v-else class="w-full text-sm bg-white border rounded shadow">
      <thead class="text-left bg-gray-100">
        <tr>
          <th class="p-3">Gambar</th>
          <th class="p-3">Nama Barang</th>
          <th class="p-3">Kategori</th>
          <th class="p-3">Stok</th>
          <th class="p-3">Kondisi</th>
          <th class="p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in barangStore.items" :key="item.id" class="border-t">
          <td class="p-3">
            <img
              v-if="item.gambar"
              :src="`/storage/${item.gambar}`"
              alt="Gambar barang"
              class="object-cover w-12 h-12 rounded"
            />
            <span v-else class="text-xs text-gray-400">-</span>
          </td>
          <td class="p-3">{{ item.nama_barang }}</td>
          <td class="p-3">{{ item.kategori }}</td>
          <td class="p-3">{{ item.stok }}</td>
          <td class="p-3 capitalize">{{ item.kondisi.replace('_', ' ') }}</td>
          <td class="flex gap-2 p-3">
            <router-link
              :to="`/admin/barang/${item.id}/edit`"
              class="text-blue-600 hover:underline"
            >
              Edit
            </router-link>
            <button @click="handleDelete(item.id)" class="text-red-600 hover:underline">
              Hapus
            </button>
          </td>
        </tr>
        <tr v-if="barangStore.items.length === 0">
          <td colspan="6" class="p-3 text-center text-gray-500">Belum ada data barang.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useBarangStore } from '../../../stores/barang';

const barangStore = useBarangStore();

onMounted(() => {
  barangStore.fetchAll();
});

async function handleDelete(id) {
  if (!confirm('Yakin ingin menghapus barang ini?')) return;
  await barangStore.remove(id);
}
</script>
