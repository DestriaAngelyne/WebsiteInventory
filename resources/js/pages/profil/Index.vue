<template>
  <div class="max-w-lg p-6 mx-auto">
    <h1 class="mb-4 text-xl font-bold">Profil Saya</h1>

    <div class="p-6 bg-white rounded shadow">
      <div class="flex items-center gap-4 mb-6">
        <img
          v-if="previewUrl"
          :src="previewUrl"
          alt="Foto profil"
          class="object-cover rounded-full w-20 h-20 border"
        />
        <div v-else class="flex items-center justify-center w-20 h-20 text-2xl font-bold text-gray-500 bg-gray-200 rounded-full">
          {{ authStore.user?.name?.charAt(0) }}
        </div>

        <div>
          <input @change="handleFileChange" type="file" accept="image/*" class="text-sm" />
          <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maksimal 5MB.</p>
        </div>
      </div>

      <p v-if="errorMessage" class="mb-3 text-sm text-red-600">{{ errorMessage }}</p>
      <p v-if="successMessage" class="mb-3 text-sm text-green-600">{{ successMessage }}</p>

      <button
        v-if="croppedFile"
        @click="handleUpload"
        :disabled="loading"
        class="px-4 py-2 mb-6 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
      >
        {{ loading ? 'Mengunggah...' : 'Simpan Foto Profil' }}
      </button>

      <div class="pt-4 space-y-2 text-sm border-t">
        <div class="flex justify-between">
          <span class="text-gray-500">Nama</span>
          <span class="font-medium">{{ authStore.user?.name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Email</span>
          <span class="font-medium">{{ authStore.user?.email }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Role</span>
          <span class="font-medium capitalize">{{ authStore.user?.role }}</span>
        </div>
        <div v-if="authStore.user?.nisn" class="flex justify-between">
          <span class="text-gray-500">NISN</span>
          <span class="font-medium">{{ authStore.user?.nisn }}</span>
        </div>
        <div v-if="authStore.user?.kelas" class="flex justify-between">
          <span class="text-gray-500">Kelas</span>
          <span class="font-medium">{{ authStore.user?.kelas }}</span>
        </div>
      </div>
    </div>

    <div v-if="showCropModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div class="w-full max-w-md p-4 bg-white rounded shadow">
        <h2 class="mb-3 text-lg font-bold">Atur Foto Profil</h2>
        <p class="mb-3 text-xs text-gray-500">Geser & perbesar area kotak untuk memilih bagian foto yang mau dipakai.</p>

        <div class="mb-4" style="max-height: 400px; overflow: hidden;">
          <img ref="cropImageRef" :src="rawImageUrl" style="max-width: 100%; display: block;" />
        </div>

        <div class="flex gap-2">
          <button @click="confirmCrop" class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Konfirmasi
          </button>
          <button @click="cancelCrop" class="px-4 py-2 text-sm border rounded hover:bg-gray-50">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const previewUrl = ref(null);
const croppedFile = ref(null);
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const showCropModal = ref(false);
const rawImageUrl = ref(null);
const cropImageRef = ref(null);
let cropperInstance = null;

onMounted(() => {
  if (authStore.user?.avatar) {
    previewUrl.value = `/storage/${authStore.user.avatar}`;
  }
});

function handleFileChange(event) {
  const selected = event.target.files[0];
  if (!selected) return;

  errorMessage.value = '';
  successMessage.value = '';
  rawImageUrl.value = URL.createObjectURL(selected);
  showCropModal.value = true;

  nextTick(() => {
    if (cropperInstance) {
      cropperInstance.destroy();
    }
    cropperInstance = new Cropper(cropImageRef.value, {
      aspectRatio: 1,
      viewMode: 1,
      guides: false,
      background: false,
      autoCropArea: 1,
      movable: true,
      zoomable: true,
    });
  });

  event.target.value = '';
}

function confirmCrop() {
  if (!cropperInstance) return;

  cropperInstance.getCroppedCanvas({ width: 400, height: 400 }).toBlob((blob) => {
    croppedFile.value = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = URL.createObjectURL(blob);
    closeCropModal();
  }, 'image/jpeg', 0.9);
}

function cancelCrop() {
  closeCropModal();
}

function closeCropModal() {
  showCropModal.value = false;
  if (cropperInstance) {
    cropperInstance.destroy();
    cropperInstance = null;
  }
  if (rawImageUrl.value) {
    URL.revokeObjectURL(rawImageUrl.value);
    rawImageUrl.value = null;
  }
}

async function handleUpload() {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await authStore.uploadAvatar(croppedFile.value);
    successMessage.value = 'Foto profil berhasil diperbarui.';
    croppedFile.value = null;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengunggah foto profil.';
  } finally {
    loading.value = false;
  }
}
</script>
