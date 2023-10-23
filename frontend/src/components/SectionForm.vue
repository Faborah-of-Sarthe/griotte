<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <template v-if="step == 1">

                    <BaseInput label="Nom" :value="sectionFormStore.section.name" v-model="sectionFormStore.section.name"></BaseInput>
                    <ColorInput label="Couleur" :value="sectionFormStore.section.color" v-model="sectionFormStore.section.color"></ColorInput>
                </template>
                <template v-if="step == 2">
                    <IconSelect :value="sectionFormStore.section.icon" v-model="sectionFormStore.section.icon"></IconSelect>
                </template>
                <div class="buttons">
                    <Button type="button" design="secondary" @click="stepDown" v-if="step > 1">Précédent</Button>
                    <Button :key="buttonType" :type="buttonType" @click="stepUp" :disabled="sectionFormStore.section.name.length === 0 && sectionFormStore.section.color.length === 0">{{ buttonLabel }}</Button>
                </div>
            </form>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="sectionFormStore.updateOpen(false)"></div>
    </Transition>
</template>

<script setup>

import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { defineProps, defineEmits, computed, ref } from 'vue'
import Card  from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import Button from '@/components/forms/Button.vue'
import ColorInput from '@/components/forms/ColorInput.vue'
import IconSelect from '@/components/forms/IconSelect.vue'
import { useSectionFormStore } from '@/stores/sectionForm'

const sectionFormStore = useSectionFormStore()

const step = ref(1)
const maxStep = 2;



// Computed 

const title = computed(() => {
    if(step.value === 2) {
        return 'Choisir une icône'
    }
    else if (sectionFormStore.type === 'add') {
        return 'Ajouter un rayon'
    } else {
        return 'Modifier un rayon'
    }
})

const buttonLabel = computed(() => {

    if(step.value === 1) {
        return 'Suivant'
    }
    else if (sectionFormStore.type === 'add') {
        return 'Ajouter à ma liste'
    } else {
        return 'Enregistrer'
    }
})

const buttonType = computed(() => {
    if (step.value === 1) {
        return 'button'
    } else {
        return 'submit'
    }
})


// Methods
const queryClient = useQueryClient()

// Section creation query
// const productCreation = useMutation({
//   mutationFn: (productData) => {
//     return axios.post(import.meta.env.VITE_API_URL + 'products/', productData)
//   },
//   onSuccess: () => {
//     queryClient.invalidateQueries('products')
//     productFormStore.updateOpen(false)
//   },
//   onError: (error) => {
//     // TODO: handle error
//     console.log(error)
//   },
// });

// Section edition query
const sectionEdition = useMutation({
  mutationFn: (sectionData) => {
    console.log(sectionData);
    return axios.patch(import.meta.env.VITE_API_URL + 'sections/' + sectionData.id, sectionData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('sections')
    sectionFormStore.updateOpen(false)
  },
  onError: (error) => {
    // TODO: handle error
    console.log(error)
    },
});



function handleSubmit() {
    if (sectionFormStore.type === 'add') {
        // addProduct()
    } else {
        sectionEdition.mutate(sectionFormStore.section)
    }
}


// Handle the product creation
// function addProduct() {

//     const productData = {
//         name: productFormStore.product.name,
//         comment: productFormStore.product.comment,
//     }

//     // Do not send section_id if it is 0
//     if (productFormStore.product.section_id !== 0) {
//         productData.section_id = productFormStore.product.section_id
//     }

//     productCreation.mutate(productData)
// }

function stepUp() {
    if (step.value < maxStep) {
        step.value++
    } 
}

function stepDown() {
    if (step.value > 1) {
        step.value--
    } 
}


</script>

<style scoped>
    .card {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 30rem;
        background: var(--color-background);
        border-radius: .5rem;
        padding: 1rem;
        z-index: 2;

    }
    .buttons {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
    }
</style>