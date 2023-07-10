<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <BaseInput label="Nom" :value="currentProduct.name" v-model="currentProduct.name"></BaseInput>
                <TextArea label="Commentaire" placeholder="Quantité, variante, recette, etc" v-model="currentProduct.comment"></TextArea>
                <SectionSelect :value="currentSection" v-model:currentSection="currentSection" ></SectionSelect> 
                
                <Button type="submit"  :disabled="currentProduct.name.length === 0">{{ buttonLabel }}</Button>
            </form>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="emit('close')"></div>
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


// Props
const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  type: {
    type: String,
    default: 'add',
  },
  section_id: {
    type: Number,
    default: 0,
  }
})

// State from props
const currentProduct = ref(props.product)
const currentSection = ref(props.section_id)


const emit = defineEmits(['close'])

// Computed 

const title = computed(() => {
    if (props.type === 'add') {
        return 'Ajouter un produit'
    } else {
        return 'Modifier un produit'
    }
})

const buttonLabel = computed(() => {
    if (props.type === 'add') {
        return 'Ajouter à ma liste'
    } else {
        return 'Enregistrer'
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
    emit('close')
  },
  onError: (error) => {
    // TODO: handle error
    console.log(error)
  },
});



function handleSubmit() {
    if (props.type === 'add') {
        addProduct()
    } else {
        // updateProduct()
    }
}


// Handle the product creation
function addProduct() {

    const productData = {
        name: currentProduct.value.name,
        comment: currentProduct.value.comment,
    }

    // Do not send section_id if it is 0
    if (currentSection.value !== 0) {
        productData.section_id = currentSection.value
    }

    productCreation.mutate(productData)
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
</style>