import { defineStore } from "pinia";

export const useActionsStore = defineStore({
    id: "actions",
    state: () => ({
        actions: [],
        visible: false,
    }),
    getters: {
        lastAction: (state) => {
            return state.actions[state.actions.length - 1];
        },
    },
    actions: {
        addAction(action) {
            this.actions.push(action);
            // Make the button visible for 5 seconds
            this.visible = true;
            setTimeout(() => {
                this.visible = false;
            }, 5000);
        },
        removeLastAction() {
            this.actions.pop();
        },
        toggleVisibility() {
            this.visible = !this.visible;
        },
    },
});