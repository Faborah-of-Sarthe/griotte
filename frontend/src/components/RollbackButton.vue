<template>
    <Transition name="slideLeft" appear>
        <button v-if="actionsStore.actions.length > 0" :disabled="isLoading" class="rollback_button" @click="rollback" >
            <Arrow class="arrow" ></Arrow>
        </button>
    </Transition>
</template>

<script setup>
import Arrow from './icons/Arrow.vue';
import { useMutation, useQueryClient } from  '@tanstack/vue-query'
import { useActionsStore } from '../stores/actions'
import { ref } from 'vue'
import axios from 'axios'

const queryClient = useQueryClient()
const actionsStore = useActionsStore()
const productId = ref('')


const { mutate, isLoading } = useMutation({
  mutationFn: (productData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'products/' + productId.value, productData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('products')
    actionsStore.removeLastAction()
  },
  onError: (error) => {
    console.log(error)
  },
});

const rollback = () => {
    const action =  actionsStore.lastAction
    productId.value = action.product.id

    mutate({
        to_buy: action.type == 'uncheck' ? 1 : 0,
        comment: action.product.comment
    })
}


</script>

<style scoped>
.rollback_button {
  background: var(--color-primary);
  border: none;
  border-radius: 50%;
  width: 2.5rem;
  height: 2.5rem;
  margin-right: 2rem;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  outline: none;
  position: fixed;
  left: 50%;
  transform: translateX(calc( -50% - 5rem))
}

.arrow {
    height: .7rem;
    position: relative;
    fill: var(--color-background);
    transform: rotate(180deg);
    transition: all 0.1s ease-in-out;
}

.rollback_button:disabled {
  background-color: var(--color-9);
}



</style>