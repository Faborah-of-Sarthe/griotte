<template>
    <h1>Mes magasins</h1>
    <div v-if="data">
        
        <!-- Loop through data -->
        <ol>
            <li v-for="store in data" :key="store.id"  :class="userStore.user.currentStore == store.id ? 'current' : ''" class="store">
                <a href="#" class="store__name">{{ store.name }}</a>
                <CheckMark class="checkbox" @click="setCurrentStore.mutate(store.id)"></CheckMark>
            </li>
        </ol>  
    </div>
</template>

<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';
import CheckMark from '../components/icons/CheckMark.vue'
import { useUserStore } from '../stores/user'

// Get the user from the store
const userStore = useUserStore()

// Load the stores from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['stores'],
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'stores', {
      withCredentials: true,

    })
    return res.data

  },
})

const queryClient = useQueryClient()

// Mutation to set the current store
const setCurrentStore = useMutation({
  mutationFn: (storeData) => {
    return axios.put(import.meta.env.VITE_API_URL + 'stores/' + storeData + '/set-current')
  },
  onSuccess: (data, store) => {
    queryClient.invalidateQueries('stores')
    userStore.setCurrentStore(store)
  },
  onError: (error) => {
    console.log(error)
  },
});

</script>

<style scoped>

    h1 {
        margin-bottom: 1rem;
    }
    ol {
        display: flex;
        flex-direction: column;
        padding-left: 0;
        counter-reset: stores;

    }
    .store {
        background: var(--color-secondary);
        font-size: 1.5rem;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    .current {
        background: var(--color-primary);
    }
    .current .store__name{
        color: var(--color-background);
    }
    .store__name{
        font-weight: 700;
        color: var(--color-primary);
    }
    li {
        display: flex;
        align-items: center;
    }
    li::before {
        counter-increment: stores;
        content: counter(stores) ".";
        font-weight: 700;
        color: var(--color-primary);
        margin-right: 1rem;
    }
 
    .current::before {
        color: var(--color-background);
    }
    .checkmark {
        stroke: var(--color-primary);
        border: 2px solid var(--color-primary);
        width: 1.5rem;
        height: 1.5rem;
        border-radius: .25rem;
        display: block;
        stroke-width: 4px;
        stroke-dashoffset: 50;
        stroke-dasharray: 50;
        transition: background-color 0.2s ease-in-out, stroke-dashoffset 0.3s ease-in-out;
        cursor: pointer;
        margin-left: auto;
    }
    .current .checkmark {
        stroke-dashoffset: 0;
        stroke: var(--color-background);
        border: 2px solid var(--color-background);
    }
</style>