import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async register(payload) {
      const response = await api.post('/register', payload);
      return response.data;
    },

    async login(payload) {
      const response = await api.post('/login', payload);
      this.setSession(response.data.user, response.data.token);
    },

    async logout() {
      await api.post('/logout');
      this.clearSession();
    },

    async uploadAvatar(file) {
      const formData = new FormData();
      formData.append('avatar', file);

      const response = await api.post('/profile/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      this.user = response.data;
      localStorage.setItem('user', JSON.stringify(response.data));
    },

    setSession(user, token) {
      this.user = user;
      this.token = token;
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('token', token);
    },

    clearSession() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('user');
      localStorage.removeItem('token');
    },
  },
});
