<script setup>
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import CheckMark from '@/components/icons/CheckMark.vue'
import Button from '@/components/forms/Button.vue'

const props = defineProps({
    recipe: {
        type: Object,
        required: true
    },
    showText: {
        type: Boolean,
        default: false
    },
    asButton: {
        type: Boolean,
        default: false
    }
})

const queryClient = useQueryClient()

const { mutate, isLoading: isLoadingMutation } = useMutation({
    mutationFn: (recipe) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + recipe.id, { to_make: !recipe.to_make })
    },
    onSuccess: (response) => {
        // Mettre à jour la recette spécifique dans le cache
        queryClient.setQueryData(['recipe', response.data.id.toString()], response.data)
        
        // Mettre à jour les données des recettes dans la liste (infinite query)
        queryClient.setQueriesData(['recipes'], (oldData) => {
            if (!oldData) return oldData;
            
            return {
                ...oldData,
                pages: oldData.pages.map(page => ({
                    ...page,
                    data: page.data.map(recipe => 
                        recipe.id === response.data.id ? response.data : recipe
                    )
                }))
            }
        })
        
        // Invalider le compteur des recettes à faire
        queryClient.invalidateQueries(['numberOfRecipesToMake'])
    },
    onError: (error) => {
        console.error('Erreur lors de la mise à jour du statut à faire:', error)
    }
})

const toggleToMake = () => {
    mutate(props.recipe)
}
</script>

<template>
    <!-- Version bouton pour la vue de détail -->
    <Button 
        v-if="asButton"
        :design="recipe.to_make ? 'primary' : 'secondary'" 
        @click="toggleToMake" 
        type="button"
        :disabled="isLoadingMutation"
        class="todo-button"
        :class="{ checked: recipe.to_make }"
    >
        <CheckMark class="checkmark" :class="{ checked: recipe.to_make }" />
        <span v-if="showText">À faire</span>
    </Button>
    
    <!-- Version icône simple pour la liste -->
    <CheckMark 
        v-else
        class="checkbox" 
        :class="{
            checked: recipe.to_make, 
            disabled: isLoadingMutation
        }" 
        @click="toggleToMake"
    />
</template>

<style lang="scss" scoped>
.todo-button {
    :deep(.checkmark) {
        margin-right: 0.5rem;
        width: 1rem;
        height: 1rem;
        stroke: var(--color-primary);
        border: 2px solid var(--color-primary);
        border-radius: 3px;
    }
    
    &.checked :deep(.checkmark) {
        stroke: var(--color-background);
        border: 2px solid var(--color-background);
    }
}

.checkbox {
    cursor: pointer;
    transition: all 0.2s ease;
}

.checkbox.checked {
    stroke: var(--color-background);
    border: 2px solid var(--color-background);
}

.checkbox.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
