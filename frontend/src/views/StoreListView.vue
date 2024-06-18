<template>
    <h1 v-if="userStore.hasStore">Mes magasins</h1>
    <h1 v-else>Bienvenue !</h1>
    <div v-if="isLoading"><Loader /></div>
    <div v-if="data">
        <ol>
            <li v-for="store in data" :key="store.id"  :class="userStore.user.currentStore == store.id ? 'current' : ''" class="store">
                <RouterLink :to="{name: 'my-store', params: {id: store.id}}" class="store__name" >
                    {{ store.name }}
                </RouterLink>
                <CheckMark class="checkbox" @click="setCurrentStore.mutate(store.id)"></CheckMark>
            </li>
        </ol>  
    </div>
    <div class="empty" v-if="!isLoading && data.length == 0 && !userStore.tutorial">
       <WelcomeCherry :step="'store'"/>
    </div>
    <div>
        <Button @click="handleNewStore" type="button" >Ajouter un magasin</Button>
    </div>
    <StoreForm v-if="storeFormStore.open"></StoreForm>
</template>

<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';
import CheckMark from '../components/icons/CheckMark.vue'
import Button from '../components/forms/Button.vue'
import StoreForm from '../components/StoreForm.vue'
import { useUserStore } from '../stores/user'
import { useStoreFormStore } from '../stores/storeForm'
import Loader from '../components/Loader.vue';
import WelcomeCherry from '../components/WelcomeCherry.vue'

// Get the user from the store
const userStore = useUserStore()
// Get the store form store
const storeFormStore = useStoreFormStore()

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


/**
 * Handle the new store by opening the form and resetting the store
 */
 function handleNewStore() {
    storeFormStore.resetStore()
    storeFormStore.updateType('add')
    storeFormStore.updateOpen(true)
}


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
    .btn {
        margin-bottom: 1rem;
    }
    .current .store__name{
        color: var(--color-background);
    }
    .store__name{
        font-weight: 700;
        color: var(--color-text);
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
 
    .current .checkmark {
        stroke: var(--color-background);
        border: 2px solid var(--color-background);
    }
    .empty {
        min-height: 50vh;
    }
</style>