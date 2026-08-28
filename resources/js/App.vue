<template>
  <div v-if="authStore.isLoggedIn && !authStore.isAdmin" class="flex min-h-screen bg-gray-50">
    <DashboardSidebar />
    <div class="flex flex-col flex-1 min-w-0">
      <router-view />
    </div>
  </div>

  <div v-else>
    <Navbar />
    <router-view />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
import DashboardSidebar from './components/siswa/DashboardSidebar.vue';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();
</script>
