import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
  state: () => ({
    showRequestModal: false,
  }),
  actions: {
    openRequestModal() {
      this.showRequestModal = true;
    },
    closeRequestModal() {
      this.showRequestModal = false;
    },
  },
});
