import { defineStore } from 'pinia';
import api from '../services/api';

export const useUsersStore = defineStore('users', {
  state: () => ({
    items: [],
    loading: false,
  }),

  actions: {
    async fetchAll() {
      this.loading = true;
      try {
        const response = await api.get('/users');
        this.items = response.data;
      } finally {
        this.loading = false;
      }
    },

    async remove(id) {
      await api.delete(`/users/${id}`);
      this.items = this.items.filter((item) => item.id !== id);
    },
  },
});
