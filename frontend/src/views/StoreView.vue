<template>
    <h1>Mes magasins</h1>
    <div v-if="data">
        
        <!-- Loop through data -->
        <ol>
            <li v-for="store in data" :key="store.id"  :class="user.currentStore == store.id ? 'current' : ''" class="store">
                <a href="#" class="store__name">{{ store.name }}</a>
            </li>
        </ol>  
    </div>
</template>

<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';
import { useUserStore } from '../stores/user'

// Get the user from the store
const {user} = useUserStore()

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

</script>

<style scoped>

    h1 {
        margin-bottom: 1rem;
    }
    ol {
        display: flex;
        flex-direction: column;
        padding-left: 0;
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
        list-style-type: decimal;
        list-style-position: inside;
    }
    li::marker {
        color: var(--color-primary);
    }
    .current::marker {
        color: var(--color-background);
    }
</style>