<script setup>

import { RouterLink } from 'vue-router'

defineProps({
    recipe: Object,
    number: Number
})


</script>

<template>
    <RouterLink :to="{ name: 'recipe', params: { id: recipe.id }}">
        <div class="recipe-card" >
            <div class="recipe-card__content">
                <p>{{ recipe.name }}</p>
            </div>
        </div>
    </RouterLink>
</template>

<style lang="scss" scoped>

.recipe-card {
    color: var(--color-text);
    background-color: var(--color-9-light);
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 1rem;
    margin-bottom: 1rem;
  
}

p {
    font-size: 1.5rem;
}

</style>