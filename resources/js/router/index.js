import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../pages/Home.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/auth/login/Index.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../pages/auth/register/Index.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/admin/barang',
    name: 'admin.barang.index',
    component: () => import('../pages/admin/barang/Index.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/admin/barang/tambah',
    name: 'admin.barang.create',
    component: () => import('../pages/admin/barang/Form.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/admin/barang/:id/edit',
    name: 'admin.barang.edit',
    component: () => import('../pages/admin/barang/Form.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/admin/peminjaman',
    name: 'admin.peminjaman.index',
    component: () => import('../pages/admin/peminjaman/Index.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/admin/laporan',
    name: 'admin.laporan.index',
    component: () => import('../pages/admin/laporan/Index.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/peminjaman',
    name: 'peminjaman.index',
    component: () => import('../pages/siswa/peminjaman/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/peminjaman/ajukan',
    name: 'peminjaman.create',
    component: () => import('../pages/siswa/peminjaman/Form.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/users',
    name: 'admin.users.index',
    component: () => import('../pages/admin/users/Index.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/notifikasi',
    name: 'notifikasi.index',
    component: () => import('../pages/siswa/notifikasi/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pengaduan',
    name: 'pengaduan.index',
    component: () => import('../pages/siswa/pengaduan/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/pengaduan',
    name: 'admin.pengaduan.index',
    component: () => import('../pages/admin/pengaduan/Index.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
  {
    path: '/profil',
    name: 'profil.index',
    component: () => import('../pages/profil/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pengaturan',
    name: 'pengaturan.index',
    component: () => import('../pages/pengaturan/Index.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next({ name: 'login' });
  }

  if (to.meta.adminOnly && !authStore.isAdmin) {
    return next({ name: 'home' });
  }

  if (to.meta.guestOnly && authStore.isLoggedIn) {
    return next({ name: 'home' });
  }

  next();
});

export default router;
