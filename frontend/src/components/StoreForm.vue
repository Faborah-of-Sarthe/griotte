<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <BaseInput autocomplete="off" label="Nom" :placeholder="randomName" :value="storeFormStore.store.name" v-model="storeFormStore.store.name"></BaseInput>
                <div v-if="userStore.hasStore">
                    <Checkbox v-if="storeFormStore.type == 'add'" class="checkmark" label="Copier les rayons d'un magasin existant ?" :checked="copySections" v-model="copySections"></Checkbox>
                    <div v-if="copySections">
                        <Select label="Magasin" :options="storesList" v-model="storeFormStore.store.copyFrom"></Select>
                    </div>
                </div>
                <div class="buttons">
                <Button type="submit" :disabled="storeFormStore.store.name.length === 0 || loadingCreation || loadingEdition">{{ buttonLabel }}</Button>
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
import { computed, ref, watch } from 'vue'
import Card  from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import Button from '@/components/forms/Button.vue'
import Select from '@/components/forms/Select.vue'
import { useStoreFormStore } from '@/stores/storeForm'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'
import Checkbox from './forms/Checkbox.vue'
import { useToast } from 'vue-toastification'

const storeFormStore = useStoreFormStore()
const userStore = useUserStore()
const router = useRouter()

const copySections = ref(false)

const queryClient = useQueryClient()
const toast = useToast()

// Get the stores list from the cache
const storeData = queryClient.getQueryData({queryKey:  ['stores']})

// Transform the stores list to an array of objects with id and name
const storesList = computed(() => {
    return storeData.map((store) => {
        return {
            value: store.id,
            name: store.name
        }
    })
})

// Generate a random name for the placeholder
const randomName = computed(() => {
    const names = ['Croisement', 'Intramarché', 'Le Clair', 'Au champ', 'Méga U', 'Uniprix', "L'idole", 'Aldo', 'Meneur prix', 'Tripot', 'Simple marché' ];
    return names[Math.floor(Math.random() * names.length)]
})

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


// Store creation query
const {mutate: storeCreationMutate, isLoading: loadingCreation } = useMutation({
  mutationFn: (storeData) => {
    return axios.post(import.meta.env.VITE_API_URL + 'stores/', storeData)
  },
  onSuccess: (data) => {
    const storeId = data.data.store.id

    if(data.data.current_store) {
        userStore.setCurrentStore(data.data.current_store)
    }

    // Redirect to the store page
    router.push({ name: 'my-store', params: { id: storeId } })
    queryClient.invalidateQueries('stores')
    storeFormStore.updateOpen(false)


    
    toast.success('Le magasin a été créé !')

  },
});

// Store edition query
const {mutate: storeEditionMutate, isLoading: loadingEdition} = useMutation({
  mutationFn: (storeData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'stores/' + storeData.id, storeData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('stores')
    storeFormStore.updateOpen(false)
  }
});


/**
 * Handle the form submission and dispatch the creation or edition 
 * 
 * */
function handleSubmit() {
    if (storeFormStore.type === 'add') {
        storeCreationMutate(storeFormStore.store)
    } else {
        storeEditionMutate(storeFormStore.store)
    }
}

/**
 * Watch the copySections value and update the store to copy from when it's set to false
 */
watch(copySections, (value) => {
    if (!value) {
        storeFormStore.updateStore({
            copyFrom: null
        })
    }
})



</script>

<style scoped>
    
    .checkmark {
        margin: 2rem 0;
    }
</style>