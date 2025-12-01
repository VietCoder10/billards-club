import type { AxiosError } from 'axios';
import { defineStore } from 'pinia';
interface OwnState {
  isFetching: boolean[];
  isLoading: boolean;
  error?: AxiosError;
}
export const useRequestStore = defineStore({
  id: 'request',
  state: (): OwnState => ({
    isFetching: [] as boolean[],
    error: undefined,
    isLoading: false
  }),
  getters: {
    getIsFetcing: (state) => state.isFetching
  },
  actions: {
    showLoading() {
      this.isLoading = true;
    },
    hideLoading() {
      this.isLoading = false;
    },
    insert() {
      this.isFetching.push(true);
    },
    remove() {
      this.isFetching.pop();
    },
    resetError() {
      this.error = undefined;
    },
    setError(value: AxiosError) {
      this.error = value;
    }
  }
});
