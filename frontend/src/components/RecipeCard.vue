<script setup>
import { RouterLink } from 'vue-router'
import TodoButton from '@/components/TodoButton.vue'

const props = defineProps({
    recipe: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <li class="recipe-card" :class="{checked: recipe.to_make}">
        <RouterLink :to="{ name: 'recipe', params: { id: recipe.id }}">
            <p class="recipe-card__name">{{ recipe.name }}</p>
        </RouterLink>
        <TodoButton :recipe="recipe" />
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
    
    a {
        margin-right: 0.5rem;
        word-break: break-word;
    }
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


</style>