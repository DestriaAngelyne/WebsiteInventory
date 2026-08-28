<template>
  <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6">
    <div class="mb-8">
      <h1 class="text-xl font-bold text-gray-900">Profil Saya</h1>
      <p class="mt-1 text-sm text-gray-500">Kelola foto dan informasi pribadi kamu.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      <!-- Kolom kiri: konteks -->
      <div class="lg:col-span-1">
        <div class="flex items-center justify-center w-10 h-10 mb-3 text-blue-600 bg-blue-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-800">Informasi Akun</h2>
        <p class="mt-1 text-sm leading-relaxed text-gray-500">
          Data ini dipakai untuk mengidentifikasi kamu di sistem peminjaman barang, jadi pastikan selalu akurat.
        </p>
      </div>

      <!-- Kolom kanan: form -->
      <div class="lg:col-span-2">
        <div class="overflow-hidden bg-white border border-gray-200 rounded-xl">
          <!-- Section: Foto Profil -->
          <div class="p-6">
            <h2 class="text-sm font-semibold text-gray-800">Foto Profil</h2>
            <p class="mt-0.5 mb-4 text-xs text-gray-500">JPG atau PNG, maksimal 5MB.</p>

            <div class="flex items-center gap-4">
              <div class="relative shrink-0">
                <img
                  v-if="previewUrl"
                  :src="previewUrl"
                  alt="Foto profil"
                  class="object-cover border w-16 h-16 rounded-full border-gray-200"
                />
                <div
                  v-else
                  class="flex items-center justify-center w-16 h-16 text-xl font-bold text-gray-500 bg-gray-100 rounded-full"
                >
                  {{ authStore.user?.name?.charAt(0) }}
                </div>
              </div>

              <div class="flex items-center gap-2">
                <label
                  class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                >
                  Ganti Foto
                  <input @change="handleFileChange" type="file" accept="image/*" class="hidden" />
                </label>

                <button
                  v-if="croppedFile"
                  @click="handleUploadAvatar"
                  :disabled="avatarLoading"
                  class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                  {{ avatarLoading ? 'Mengunggah...' : 'Simpan Foto' }}
                </button>
              </div>
            </div>

            <p v-if="avatarError" class="px-3 py-2 mt-3 text-xs text-red-700 bg-red-50 rounded-lg">
              {{ avatarError }}
            </p>
            <p v-if="avatarSuccess" class="px-3 py-2 mt-3 text-xs text-green-700 bg-green-50 rounded-lg">
              {{ avatarSuccess }}
            </p>
          </div>

          <!-- Section: Informasi Pribadi -->
          <div class="p-6 border-t border-gray-100">
            <h2 class="mb-4 text-sm font-semibold text-gray-800">Informasi Pribadi</h2>

            <p v-if="profileError" class="px-3 py-2 mb-4 text-xs text-red-700 bg-red-50 rounded-lg">{{ profileError }}</p>
            <p v-if="profileSuccess" class="px-3 py-2 mb-4 text-xs text-green-700 bg-green-50 rounded-lg">{{ profileSuccess }}</p>

            <div class="space-y-4">
              <div>
                <label class="block mb-1.5 text-xs font-medium text-gray-600">Nama</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <div>
                <label class="block mb-1.5 text-xs font-medium text-gray-600">Email</label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <div v-if="authStore.user?.role === 'siswa'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="block mb-1.5 text-xs font-medium text-gray-600">NISN</label>
                  <input
                    v-model="profileForm.nisn"
                    type="text"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block mb-1.5 text-xs font-medium text-gray-600">Kelas</label>
                  <input
                    v-model="profileForm.kelas"
                    type="text"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-5 border-t border-gray-100">
              <router-link to="/pengaturan" class="text-xs font-medium text-blue-600 hover:underline">
                Ganti password? Buka Pengaturan →
              </router-link>

              <button
                @click="handleSaveProfile"
                :disabled="profileLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ profileLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal crop foto -->
    <div v-if="showCropModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      <div class="w-full max-w-md p-5 bg-white rounded-xl">
        <h2 class="mb-1 text-base font-semibold text-gray-900">Atur Foto Profil</h2>
        <p class="mb-4 text-xs text-gray-500">Geser & perbesar area kotak untuk memilih bagian foto yang mau dipakai.</p>

        <div class="mb-4 overflow-hidden border border-gray-200 rounded-lg" style="max-height: 400px;">
          <img ref="cropImageRef" :src="rawImageUrl" style="max-width: 100%; display: block;" />
        </div>

        <div class="flex justify-end gap-2">
          <button @click="cancelCrop" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
            Batal
          </button>
          <button @click="confirmCrop" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Konfirmasi
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

// --- Avatar ---
const previewUrl = ref(null);
const croppedFile = ref(null);
const avatarLoading = ref(false);
const avatarError = ref('');
const avatarSuccess = ref('');

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

  avatarError.value = '';
  avatarSuccess.value = '';
  rawImageUrl.value = URL.createObjectURL(selected);
  showCropModal.value = true;

  nextTick(() => {
    if (cropperInstance) cropperInstance.destroy();
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

async function handleUploadAvatar() {
  avatarLoading.value = true;
  avatarError.value = '';
  avatarSuccess.value = '';

  try {
    await authStore.uploadAvatar(croppedFile.value);
    avatarSuccess.value = 'Foto profil berhasil diperbarui.';
    croppedFile.value = null;
  } catch (error) {
    avatarError.value = error.response?.data?.message || 'Gagal mengunggah foto profil.';
  } finally {
    avatarLoading.value = false;
  }
}

// --- Edit Profil (data) ---
const profileForm = reactive({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  nisn: authStore.user?.nisn || '',
  kelas: authStore.user?.kelas || '',
});
const profileLoading = ref(false);
const profileError = ref('');
const profileSuccess = ref('');

async function handleSaveProfile() {
  profileLoading.value = true;
  profileError.value = '';
  profileSuccess.value = '';

  try {
    await authStore.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
      nisn: profileForm.nisn,
      kelas: profileForm.kelas,
    });
    profileSuccess.value = 'Profil berhasil diperbarui.';
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Gagal menyimpan perubahan profil.';
  } finally {
    profileLoading.value = false;
  }
}
</script>
