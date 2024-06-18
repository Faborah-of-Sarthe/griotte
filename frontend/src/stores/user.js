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
        },
        hasStore: (state) => {
            return state.user?.currentStore != "" && state.user?.currentStore != null;
        },
        currentStore: (state) => {
            return state.user?.currentStore;
        },
        tutorial: (state) => {
            return state.user?.finished_tutorial;
        },
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
        },
        setTutorial() {
            this.user.finished_tutorial = true;
        },
    }
});