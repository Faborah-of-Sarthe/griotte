import { defineStore } from "pinia";
import axios from "axios";

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
            // Make the button visible for 10 seconds
            this.visible = true;
            setTimeout(() => {
                const index = this.actions.indexOf(action);
                // Already rolled back: nothing left to expire.
                if (index === -1) {
                    return;
                }

                // A temporary product not rolled back is a one-shot purchase,
                // so delete it for good.
                if (action.type === "uncheck" && action.product?.is_temporary) {
                    axios
                        .delete(import.meta.env.VITE_API_URL + "products/" + action.product.id)
                        .catch(() => {});
                }

                // Drop the expired action from the undo stack.
                this.actions.splice(index, 1);
                this.visible = this.actions.length > 0;
            }, 10000);
        },
        removeLastAction() {
            this.actions.pop();
        },
        toggleVisibility() {
            this.visible = !this.visible;
        },
    },
});
