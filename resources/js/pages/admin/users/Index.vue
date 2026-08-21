<template>
  <div class="p-6">
    <h1 class="mb-4 text-xl font-bold">Manajemen User</h1>

    <div v-if="usersStore.loading" class="text-gray-500">Memuat data...</div>

    <table v-else class="w-full text-sm bg-white border rounded shadow">
      <thead class="text-left bg-gray-100">
        <tr>
          <th class="p-3">Nama</th>
          <th class="p-3">Email</th>
          <th class="p-3">Terdaftar Sejak</th>
          <th class="p-3">Terakhir Aktif</th>
          <th class="p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in usersStore.items" :key="item.id" class="border-t">
          <td class="p-3">{{ item.name }}</td>
          <td class="p-3">{{ item.email }}</td>
          <td class="p-3">{{ formatTanggal(item.created_at) }}</td>
          <td class="p-3">
            <span v-if="item.last_login_at">{{ formatTanggal(item.last_login_at) }}</span>
            <span v-else class="text-gray-400">Belum pernah login</span>
          </td>
          <td class="p-3">
            <button @click="handleDelete(item.id)" class="text-red-600 hover:underline">
              Hapus
            </button>
          </td>
        </tr>
        <tr v-if="usersStore.items.length === 0">
          <td colspan="5" class="p-3 text-center text-gray-500">Belum ada user terdaftar.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useUsersStore } from '../../../stores/users';

const usersStore = useUsersStore();

onMounted(() => {
  usersStore.fetchAll();
});

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

async function handleDelete(id) {
  if (!confirm('Yakin ingin menghapus akun user ini? Data tidak bisa dikembalikan.')) return;
  try {
    await usersStore.remove(id);
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menghapus user.');
  }
}
</script>
