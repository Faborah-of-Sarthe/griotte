<script setup>
import { RouterLink } from 'vue-router'
import CheckMark from '@/components/icons/CheckMark.vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    recipe: {
        type: Object,
        required: true
    }
})

const queryClient = useQueryClient()

const { mutate, isLoading: isLoadingMutation } = useMutation({
    mutationFn: (recipe) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + recipe.id, { to_make: !recipe.to_make })
    },
    onSuccess: (response) => {
        // update the recipes data in the query cache instead of invalidating the query 
        // to avoid re-fetching all the infinite loaded data from the server
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
    },
    onError: () => {
    }
})
</script>

<template>
    <li class="recipe-card" :class="{checked: recipe.to_make}">
        <RouterLink :to="{ name: 'recipe', params: { id: recipe.id }}">
            <p class="recipe-card__name">{{ recipe.name }}</p>
        </RouterLink>
        <CheckMark 
            class="checkbox" 
            :class="{
                checked: recipe.to_make, 
                disabled: isLoadingMutation
            }" 
            @click="mutate(recipe)"
        ></CheckMark>
    </li>
</template>

<style lang="scss" scoped>
.recipe-card {
    background: var(--color-secondary);
    font-size: 1.5rem;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.recipe-card__name {
    font-weight: 700;
    color: var(--color-text);
}

.checked {
    background: var(--color-primary);
}

.checked .recipe-card__name {
    color: var(--color-background);
}

.checked .checkbox {
    stroke: var(--color-background);
    border: 2px solid var(--color-background);
}

.checkbox.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>