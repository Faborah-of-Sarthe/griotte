<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <template v-if="step == 1">

                    <BaseInput label="Nom" :value="productFormStore.product.name" v-model="productFormStore.product.name"></BaseInput>
                    <TextArea label="Commentaire" placeholder="Quantité, variante, recette, etc" v-model="productFormStore.product.comment"></TextArea>
                    
                </template>
                <template v-if="step == 2">

                    <SectionSelect :value="productFormStore.product.section_id" v-model="productFormStore.product.section_id" ></SectionSelect>
                    
                </template>
                <div class="buttons">
                    <Button type="button" design="secondary" @click="stepDown" v-if="step > 1">Précédent</Button>
                    <Button :key="buttonType" :type="buttonType" @click="stepUp" :disabled="productFormStore.product.name.length === 0">{{ buttonLabel }}</Button>
                </div>
            </form>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="productFormStore.updateOpen(false)"></div>
    </Transition>
</template>

<script setup>

import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { defineProps, defineEmits, computed, ref } from 'vue'
import Card  from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import TextArea from '@/components/forms/TextArea.vue'
import Button from '@/components/forms/Button.vue'
import SectionSelect from '@/components/forms/SectionSelect.vue'
import { useProductFormStore } from '../stores/productForm'


const productFormStore = useProductFormStore()


const step = ref(1)
const maxStep = 2;



// Computed 

const title = computed(() => {
    if(step.value === 2) {
        return 'Choisir un rayon'
    }
    else if (productFormStore.type === 'add') {
        return 'Ajouter un produit'
    } else {
        return 'Modifier un produit'
    }
})

const buttonLabel = computed(() => {

    if(step.value === 1) {
        return 'Suivant'
    }
    else if (productFormStore.type === 'add') {
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

// Product creation query
const productCreation = useMutation({
  mutationFn: (productData) => {
    return axios.post(import.meta.env.VITE_API_URL + 'products/', productData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('products')
    productFormStore.updateOpen(false)
  }
});

// Product edition query
const productEdition = useMutation({
  mutationFn: (productData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'products/' + productData.id, productData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('products')
    productFormStore.updateOpen(false)
  }
});



function handleSubmit() {
    if (productFormStore.type === 'add') {
        addProduct()
    } else {
        productEdition.mutate(productFormStore.product)
    }
}


// Handle the product creation
function addProduct() {

    const productData = {
        name: productFormStore.product.name,
        comment: productFormStore.product.comment,
    }

    // Do not send section_id if it is 0
    if (productFormStore.product.section_id !== 0) {
        productData.section_id = productFormStore.product.section_id
    }

    productCreation.mutate(productData)
}

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

    .buttons {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
    }
</style>