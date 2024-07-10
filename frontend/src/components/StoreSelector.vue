<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { ref, computed } from 'vue'
import { useUserStore } from '../stores/user'
import Arrow from './icons/Arrow.vue'

const open = ref(false)
// Props
const props = defineProps({
    stores: {
        required: true
    },
  
})

// Get the user from the store
const userStore = useUserStore()
const queryClient = useQueryClient()

// Mutation to set the current store
const setCurrentStore = useMutation({
  mutationFn: (storeData) => {
    return axios.put(import.meta.env.VITE_API_URL + 'stores/' + storeData + '/set-current')
  },
  onSuccess: (data, store) => {
    queryClient.invalidateQueries({ queryKey: ['products'], refetchType: 'all' })
    userStore.setCurrentStore(store)
  },
  onError: (error) => {
    console.log(error)
  },
});
console.log(props);

// Get the current store name from the id
const getCurrentStore = computed((id) => {
  return props.stores.value.find(store => store.id === id).name
})
</script>

<template>
  <div class="stores dropdown">
    <span class="option current" @click="open = !open">
        {{ getCurrentStore(userStore.currentStore) }} 
        <Arrow class="arrow" :class="{open: open}" v-if="stores.length > 1"></Arrow>
    </span>
    <Transition name="slideDown">
      <div class="options" v-if="open">
        <template v-for="store in stores" :key="store.id">
            <span @click="setCurrentStore.mutate(store.id)" class="option">{{ store.name }}</span>
        </template>
      </div>
    </Transition>
  </div>
</template>

<style scoped lang="scss">
  .stores.dropdown {
    color: var(--color-primary);
  }
  .options {
    transition: all 0.15s ease-in-out;
  }
  .option.current {
    display: flex;
    align-items: center;
    cursor: pointer;
  }
  .arrow {
    height: 1rem;
    padding-left: 0.25rem;
    transform: rotate(90deg);
    transition: all 0.1s ease-in-out;
    margin-left: .25em;
  }
</style>