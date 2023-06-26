<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <template v-if="step == 1">
                <BaseInput label="Nom" :value="product.name" v-model="product.name"></BaseInput>
                <TextArea label="Commentaire" placeholder="Quantité, variante, recette, etc" v-model="product.comment"></TextArea>
            </template>
            <template v-if="step == 2">
                <p>TODO RAYONS</p>
            </template>
            <!-- TODO: prev button -->
            <Button type="button" @click="stepUp" :disabled="product.name.length === 0">{{ buttonLabel }}</Button>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="emit('close')"></div>
    </Transition>
</template>

<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { defineProps, defineEmits, computed, ref } from 'vue'
import Card  from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import TextArea from '@/components/forms/TextArea.vue'
import Button from '@/components/forms/Button.vue'


// Props
const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  type: {
    type: String,
    default: 'add',
  }

})

// State
const step = ref(1)
const maxStep = 2;


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
    if (step.value === 1) {
        return 'Suivant'
    } else {
        return 'Ajouter à ma liste'
    }
})

// Methods

function stepUp() {
    if (step.value < maxStep) {
        step.value++
    } else {
        addProduct()
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
</style>