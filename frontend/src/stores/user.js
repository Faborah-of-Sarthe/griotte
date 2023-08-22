import { defineStore } from "pinia";

export const useUserStore = defineStore({
    id: "user",
    state: () => ({
        user: null,
    }),
    persist: true,
    getters: {
        isLoggedIn: (state) => {
            return state.user !== null;
        }
    },
    actions: {
        setUser(user) {
            this.user = user;
        },
        logout() {
            this.user = null;
        },
        setCurrentStore(store) {
            this.user.currentStore = store;
        }
    }
});