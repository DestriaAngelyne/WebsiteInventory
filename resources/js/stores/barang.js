import { defineStore } from 'pinia';
import api from '../services/api';

export const useBarangStore = defineStore('barang', {
  state: () => ({
    items: [],
    loading: false,
  }),

  actions: {
    async fetchAll() {
      this.loading = true;
      try {
        const response = await api.get('/barang');
        this.items = response.data;
      } finally {
        this.loading = false;
      }
    },

    async fetchTersedia() {
      this.loading = true;
      try {
        const response = await api.get('/barang-tersedia');
        this.items = response.data;
      } finally {
        this.loading = false;
      }
    },

    async create(formData) {
      const response = await api.post('/barang', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      this.items.unshift(response.data);
    },

    async update(id, formData) {
      formData.append('_method', 'PUT');
      const response = await api.post(`/barang/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      const index = this.items.findIndex((item) => item.id === id);
      if (index !== -1) this.items[index] = response.data;
    },

    async remove(id) {
      await api.delete(`/barang/${id}`);
      this.items = this.items.filter((item) => item.id !== id);
    },
  },
});
