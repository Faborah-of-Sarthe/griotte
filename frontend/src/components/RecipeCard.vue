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
        <RouterLink :to="{ name: 'recipe', params: { id: recipe.id }}" class="recipe-card__content">
            <p class="recipe-card__name">{{ recipe.name }}</p>
            <div v-if="recipe.tags && recipe.tags.length > 0" class="recipe-card__tags">
                <span v-for="tag in recipe.tags" :key="tag.id" class="recipe-card__tag">
                    {{ tag.name }}
                </span>
            </div>
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
    justify-content: space-between;
    
    a {
        margin-right: 0.5rem;
        word-break: break-word;
    }
}

.recipe-card__content {
    flex: 1;
}

.recipe-card__name {
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: 0.25rem;
}

.recipe-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.recipe-card__tag {
    background: var(--color-1-light);
    border: 1px solid transparent;
    border-radius: 0.375rem;
    color: var(--color-primary);
    font-size: 0.8rem;
    font-weight: 700;
    line-height: 1.2;
    padding: 0.2rem 0.45rem;
}

.checked {
    background: var(--color-1-light);
}

.checked .recipe-card__name {
    color: var(--color-text);
}


</style>