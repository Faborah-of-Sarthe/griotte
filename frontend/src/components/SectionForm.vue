<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <div>
                    <div v-show="step == 1">
                        
                        <BaseInput autocomplete="off" label="Nom" :value="sectionFormStore.section.name" v-model="sectionFormStore.section.name"></BaseInput>
                        <ColorInput label="Couleur" :value="sectionFormStore.section.color" v-model="sectionFormStore.section.color"></ColorInput>
                    </div>
                    <div v-show="step == 2">
                        <IconSelect :value="sectionFormStore.section.icon" v-model="sectionFormStore.section.icon"></IconSelect>
                    </div>
                </div>
                <div class="buttons">
                    <Button type="button" design="secondary" @click="stepDown" v-show="step > 1">Précédent</Button>
                    <Button key="button-next" type="button" v-show="step < maxStep"  @click="stepUp" :disabled="sectionFormStore.section.name.length === 0 || sectionFormStore.section.color.length === 0">Suivant</Button>
                    <Button key="button-submit" type="submit"  v-show="step == maxStep"  :disabled="sectionFormStore.section.icon.length === 0 || loadingCreation || loadingEdition" :loading="loadingCreation || loadingEdition">{{ buttonLabel }}</Button>
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
import { useToast } from 'vue-toastification'

const sectionFormStore = useSectionFormStore()
const toast = useToast()

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
        return 'Ajouter'
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
const {mutate: sectionCreationMutate, isLoading: loadingCreation} = useMutation({
  mutationFn: (sectionData) => {
    return axios.post(import.meta.env.VITE_API_URL + 'sections', sectionData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('sections')
    sectionFormStore.updateOpen(false)
  }
});

// Section edition query
const {mutate: sectionEditionMutate, isLoading: loadingEdition} = useMutation({
  mutationFn: (sectionData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'sections/' + sectionData.id, sectionData)
  },
  onSuccess: () => {
    queryClient.invalidateQueries('sections')
    sectionFormStore.updateOpen(false)
  }
});


/**
 * Handle the form submission and dispatch the creation or edition p*/
function handleSubmit() {
    if (sectionFormStore.type === 'add') {
        sectionCreationMutate(sectionFormStore.section)
    } else {
        sectionEditionMutate(sectionFormStore.section)
    }
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