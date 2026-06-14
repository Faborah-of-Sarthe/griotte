<template>
    <Transition name="slideUp" appear>
        <Card :title="title">
            <form @submit.prevent="handleSubmit">
                <BaseInput
                    autocomplete="off"
                    label="Nom"
                    v-model="tagFormStore.tag.name"
                />
                <div class="buttons">
                    <Button type="button" design="secondary" @click="tagFormStore.updateOpen(false)">Annuler</Button>
                    <Button
                        type="submit"
                        :disabled="tagFormStore.tag.name.length === 0 || loadingCreation || loadingEdition"
                        :loading="loadingCreation || loadingEdition"
                    >
                        {{ buttonLabel }}
                    </Button>
                </div>
            </form>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="tagFormStore.updateOpen(false)"></div>
    </Transition>
</template>

<script setup>
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { computed } from 'vue'
import Card from '@/components/forms/Card.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import Button from '@/components/forms/Button.vue'
import { useTagFormStore } from '@/stores/tagForm'

const tagFormStore = useTagFormStore()
const queryClient = useQueryClient()

const title = computed(() => tagFormStore.type === 'add' ? 'Ajouter un tag' : 'Modifier un tag')
const buttonLabel = computed(() => tagFormStore.type === 'add' ? 'Ajouter' : 'Enregistrer')

const { mutate: tagCreationMutate, isLoading: loadingCreation } = useMutation({
    mutationFn: (tagData) => {
        return axios.post(import.meta.env.VITE_API_URL + 'tags', tagData)
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['tags'])
        tagFormStore.updateOpen(false)
    }
})

const { mutate: tagEditionMutate, isLoading: loadingEdition } = useMutation({
    mutationFn: (tagData) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'tags/' + tagData.id, tagData)
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['tags'])
        queryClient.invalidateQueries(['recipes'])
        tagFormStore.updateOpen(false)
    }
})

function handleSubmit() {
    if (tagFormStore.type === 'add') {
        tagCreationMutate(tagFormStore.tag)
    } else {
        tagEditionMutate(tagFormStore.tag)
    }
}
</script>

<style scoped>
.buttons {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
}
</style>
