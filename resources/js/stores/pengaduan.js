import { defineStore } from 'pinia';
import api from '../services/api';

export const usePengaduanStore = defineStore('pengaduan', {
  state: () => ({
    myItems: [],
    adminItems: [],
    loading: false,
  }),

  actions: {
    async fetchMyPengaduan() {
      this.loading = true;
      try {
        const response = await api.get('/my-pengaduan');
        this.myItems = response.data;
      } finally {
        this.loading = false;
      }
    },

    async buat(payload) {
      const response = await api.post('/pengaduan', payload);
      this.myItems.unshift(response.data);
    },

    async fetchAllForAdmin() {
      this.loading = true;
      try {
        const response = await api.get('/pengaduan');
        this.adminItems = response.data;
      } finally {
        this.loading = false;
      }
    },

    async fetchDetail(id) {
      const response = await api.get(`/pengaduan/${id}`);
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
      return response.data;
    },

    async selesaikan(id, tanggapan) {
      const response = await api.post(`/pengaduan/${id}/selesaikan`, {
        tanggapan_admin: tanggapan,
      });
      const index = this.adminItems.findIndex((item) => item.id === id);
      if (index !== -1) this.adminItems[index] = response.data;
    },
  },
});
