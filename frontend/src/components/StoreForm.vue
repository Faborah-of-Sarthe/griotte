<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <BaseInput autocomplete="off" label="Nom" :value="storeFormStore.store.name" v-model="storeFormStore.store.name"></BaseInput>
                <div class="buttons">
                    <Button  type="submit" :disabled="storeFormStore.store.name.length === 0">{{ buttonLabel }}</Button>
                </div>
            </form>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="storeFormStore.updateOpen(false)"></div>
    </Transition>
</template>

<script setup>

import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { defineProps, defineEmits, computed, ref } from 'vue'
import Card  from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import Button from '@/components/forms/Button.vue'
import { useStoreFormStore } from '@/stores/storeForm'
import { useRouter } from 'vue-router'

const storeFormStore = useStoreFormStore()
const router = useRouter()


// Computed 

const title = computed(() => { 
    if (storeFormStore.type === 'add') {
        return 'Nouveau magasin'
    } else {
        return 'Modifier le magasin'
    }
})

const buttonLabel = computed(() => {

    if (storeFormStore.type === 'add') {
        return 'Créer'
    } else {
        return 'Enregistrer'
    }
})



// Methods
const queryClient = useQueryClient()

// Store creation query
const storeCreation = useMutation({
  mutationFn: (storeData) => {
    return axios.post(import.meta.env.VITE_API_URL + 'stores/', storeData)
  },
  onSuccess: (data) => {
    queryClient.invalidateQueries('stores')
    storeFormStore.updateOpen(false)

    let storeId = data.data.id

    // Redirect to the store page
    router.push({ name: 'my-store', params: { id: storeId } })
    
    // TODO: handle success message
  },
  onError: (error) => {
    // TODO: handle error
    console.log(error)
  },
});

// Section edition query
// const sectionEdition = useMutation({
//   mutationFn: (sectionData) => {
//     console.log(sectionData);
//     return axios.patch(import.meta.env.VITE_API_URL + 'sections/' + sectionData.id, sectionData)
//   },
//   onSuccess: () => {
//     queryClient.invalidateQueries('sections')
//     storeFormStore.updateOpen(false)
//   },
//   onError: (error) => {
//     // TODO: handle error
//     console.log(error)
//     },
// });


/**
 * Handle the form submission and dispatch the creation or edition 
 * 
 * */
function handleSubmit() {
    if (storeFormStore.type === 'add') {
        storeCreation.mutate(storeFormStore.store)
    } else {
        // sectionEdition.mutate(storeFormStore.store)
    }
}



</script>

