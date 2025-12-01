import { defineStore } from "pinia";
interface Toast {
    message: string;
    mode: string;
}
export const useToastStore = defineStore({
    id: "toast",
    state: (): Toast => ({
        message: "",
        mode: "",
    }),
    getters: {
        getToast: (state) => state,
    },
    actions: {
        setToast(message, mode) {
            this.message = message;
            this.mode = mode;
        },
    },
});
