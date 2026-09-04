<template>
  <div class="max-w-4xl px-4 py-8 mx-auto sm:px-6">
    <div class="mb-8">
      <h1 class="text-xl font-bold text-foreground">Profil Saya</h1>
      <p class="mt-1 text-sm text-muted-foreground">Kelola foto dan informasi pribadi kamu.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      <!-- Kolom kiri: konteks -->
      <div class="lg:col-span-1">
        <div class="flex items-center justify-center w-10 h-10 mb-3 rounded-lg bg-secondary text-primary">
          <User class="w-5 h-5" />
        </div>
        <h2 class="text-sm font-semibold text-foreground">Informasi Akun</h2>
        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">
          Data ini dipakai untuk mengidentifikasi kamu di sistem peminjaman barang, jadi pastikan selalu akurat.
        </p>
      </div>

      <!-- Kolom kanan: form -->
      <div class="lg:col-span-2">
        <div class="overflow-hidden border rounded-xl border-border bg-card">
          <!-- Section: Foto Profil -->
          <div class="p-6">
            <h2 class="text-sm font-semibold text-card-foreground">Foto Profil</h2>
            <p class="mt-0.5 mb-4 text-xs text-muted-foreground">JPG atau PNG, maksimal 5MB.</p>

            <div class="flex items-center gap-4">
              <div class="relative shrink-0">
                <img
                  v-if="previewUrl"
                  :src="previewUrl"
                  alt="Foto profil"
                  class="object-cover border w-16 h-16 rounded-full border-border"
                />
                <div
                  v-else
                  class="flex items-center justify-center w-16 h-16 text-xl font-bold rounded-full bg-muted text-muted-foreground"
                >
                  {{ authStore.user?.name?.charAt(0) }}
                </div>
              </div>

              <div class="flex items-center gap-2">
                <label
                  class="rounded-lg border border-border bg-white px-3 py-2 text-xs font-medium text-foreground cursor-pointer hover:bg-muted"
                >
                  Ganti Foto
                  <input @change="handleFileChange" type="file" accept="image/*" class="hidden" />
                </label>

                <button
                  v-if="croppedFile"
                  @click="handleUploadAvatar"
                  :disabled="avatarLoading"
                  class="px-3 py-2 text-xs font-medium rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8] disabled:opacity-50"
                >
                  {{ avatarLoading ? 'Mengunggah...' : 'Simpan Foto' }}
                </button>
              </div>
            </div>

            <p v-if="avatarError" class="px-3 py-2 mt-3 text-xs rounded-lg text-danger-foreground bg-danger">
              {{ avatarError }}
            </p>
            <p v-if="avatarSuccess" class="px-3 py-2 mt-3 text-xs rounded-lg text-success-foreground bg-success">
              {{ avatarSuccess }}
            </p>
          </div>

          <!-- Section: Informasi Pribadi -->
          <div class="p-6 border-t border-border">
            <h2 class="mb-4 text-sm font-semibold text-card-foreground">Informasi Pribadi</h2>

            <p v-if="profileError" class="px-3 py-2 mb-4 text-xs rounded-lg text-danger-foreground bg-danger">{{ profileError }}</p>
            <p v-if="profileSuccess" class="px-3 py-2 mb-4 text-xs rounded-lg text-success-foreground bg-success">{{ profileSuccess }}</p>

            <div class="space-y-4">
              <div>
                <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Nama</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  class="w-full rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
              </div>

              <div>
                <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Email</label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  class="w-full rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
              </div>

              <div v-if="authStore.user?.role === 'siswa'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="block mb-1.5 text-xs font-medium text-muted-foreground">NISN</label>
                  <input
                    v-model="profileForm.nisn"
                    type="text"
                    class="w-full rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                  />
                </div>

                <div>
                  <label class="block mb-1.5 text-xs font-medium text-muted-foreground">Kelas</label>
                  <input
                    v-model="profileForm.kelas"
                    type="text"
                    class="w-full rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                  />
                </div>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-5 border-t border-border">
              <router-link to="/pengaturan" class="text-xs font-medium text-primary hover:underline">
                Ganti password? Buka Pengaturan →
              </router-link>

              <button
                @click="handleSaveProfile"
                :disabled="profileLoading"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8] disabled:opacity-50"
              >
                {{ profileLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal crop foto -->
    <div v-if="showCropModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div class="w-full max-w-md p-5 bg-white rounded-2xl">
        <h2 class="mb-1 text-base font-semibold text-card-foreground">Atur Foto Profil</h2>
        <p class="mb-4 text-xs text-muted-foreground">Geser & perbesar area kotak untuk memilih bagian foto yang mau dipakai.</p>

        <div class="mb-4 overflow-hidden border rounded-lg border-border" style="max-height: 400px;">
          <img ref="cropImageRef" :src="rawImageUrl" style="max-width: 100%; display: block;" />
        </div>

        <div class="flex justify-end gap-2">
          <button @click="cancelCrop" class="px-4 py-2 text-sm font-medium border rounded-lg border-border text-foreground hover:bg-muted">
            Batal
          </button>
          <button @click="confirmCrop" class="px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-[#1d4ed8]">
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
import { User } from 'lucide-vue-next';
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
