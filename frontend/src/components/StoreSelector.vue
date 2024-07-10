<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { ref, computed } from 'vue'
import { useUserStore } from '../stores/user'
import Arrow from './icons/Arrow.vue'

const open = ref(false)
const currentStoreName = ref('')

// Get the user from the store
const userStore = useUserStore()
const queryClient = useQueryClient()

// Mutation to set the current store
const setCurrentStore = useMutation({
  mutationFn: (storeData) => {
    return axios.put(import.meta.env.VITE_API_URL + 'stores/' + storeData + '/set-current')
  },
  onSuccess: (data, storeId) => {
    queryClient.invalidateQueries({ queryKey: ['storeslist'], refetchType: 'all' })
    queryClient.invalidateQueries({ queryKey: ['products'], refetchType: 'all' })
    userStore.setCurrentStore(storeId)

    currentStoreName.value = data.data.store.name
    open.value = false
  },
  onError: (error) => {
    console.log(error)
  },
});

// Get all stores
const {data} = useQuery({
  queryKey:  ['storeslist'],
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'stores', {
      withCredentials: true,

    })

    res.data.forEach(store => {
      if(store.id === userStore.currentStore) {
        currentStoreName.value = store.name
      }
    })
    

    return res.data

  },
})

const openStoreSelector = (data) => {
  if(data && data.length > 1) {
    open.value = !open.value
  }
}



</script>

<template>
  <div class="stores dropdown">
    <span class="option current" :class="{openable: data && data.length > 1}" @click="openStoreSelector(data)">
        {{currentStoreName }} 
        <Arrow class="arrow"  v-if="data && data.length > 1"></Arrow>
    </span>
    <Transition name="slideDown">
      <div class="options" v-if="open">
        <template v-for="store in data" :key="store.id">
            <div @click="setCurrentStore.mutate(store.id)" class="option">{{ store.name }}</div>
        </template>
      </div>
    </Transition>
  </div>
</template>

<style scoped lang="scss">
  .stores {
    margin-left: .5rem;
  }
  .stores.dropdown {
    color: var(--color-primary);
  }
  .options {
    transition: all 0.15s ease-in-out;
    position: absolute;
    background-color: white;
    padding: 0.5rem;
    font-size: .8em;
    border-radius: 0 0 0.5rem 0.5rem;
    z-index: 2;
  }
  .openable {
    cursor: pointer;
  }
  .options .option {
    padding: 0.5rem;
    cursor: pointer;
    
  }
  .option.current {
    display: flex;
    align-items: center;
  }
  .arrow {
    height: 1rem;
    padding-left: 0.25rem;
    transform: rotate(90deg);
    transition: all 0.1s ease-in-out;
    margin-left: .25em;
  }
</style>