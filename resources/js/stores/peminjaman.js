import { defineStore } from 'pinia';
import api from '../services/api';

export const usePeminjamanStore = defineStore('peminjaman', {
  state: () => ({
    myItems: [],
    adminItems: [],
    loading: false,
  }),

  actions: {
    async fetchMyPeminjaman() {
      this.loading = true;
      try {
        const response = await api.get('/my-peminjaman');
        this.myItems = response.data;
      } finally {
        this.loading = false;
      }
    },

    async ajukan(payload) {
      const response = await api.post('/peminjaman', payload);
      this.myItems.unshift(response.data);
    },

    async kembalikan(id, catatan = '') {
      const response = await api.post(`/peminjaman/${id}/kembalikan`, {
        catatan_pengembalian: catatan,
      });
      const index = this.myItems.findIndex((item) => item.id === id);
      if (index !== -1) this.myItems[index] = response.data;
    },

    async batalkan(id) {
      const response = await api.post(`/peminjaman/${id}/batalkan`);
      const index = this.myItems.findIndex((item) => item.id === id);
      if (index !== -1) this.myItems[index] = response.data;
    },

    async mintaPerpanjangan(id, tanggalBaru) {
      const response = await api.post(`/peminjaman/${id}/minta-perpanjangan`, {
        tanggal_kembali_diminta: tanggalBaru,
      });
      const index = this.myItems.findIndex((item) => item.id === id);
      if (index !== -1) this.myItems[index] = response.data;
    },

    async fetchAllForAdmin() {
      this.loading = true;
      try {
        const response = await api.get('/peminjaman');
        this.adminItems = response.data;
      } finally {
        this.loading = false;
      }
    },

    async approve(id) {
      const response = await api.post(`/peminjaman/${id}/approve`);
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },

    async reject(id, catatan = '') {
      const response = await api.post(`/peminjaman/${id}/reject`, { catatan_admin: catatan });
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },

    async verifikasi(id, kondisiPengembalian) {
      const response = await api.post(`/peminjaman/${id}/verifikasi`, {
        kondisi_pengembalian: kondisiPengembalian,
      });
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },

    async approvePerpanjangan(id) {
      const response = await api.post(`/peminjaman/${id}/approve-perpanjangan`);
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },

    async rejectPerpanjangan(id) {
      const response = await api.post(`/peminjaman/${id}/reject-perpanjangan`);
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },
  },
});
