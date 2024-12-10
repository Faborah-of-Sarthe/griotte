<script setup>
import { useInfiniteQuery } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import Button from '@/components/forms/Button.vue'
import RecipeCard from '../components/RecipeCard.vue'

const fetchRecipes = async ({pageParam = 1}) => {
    const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes', {
      params: {
        page: pageParam
      }
    })

    return res.data
}

const { data, error, fetchNextPage, isLoading,isFetching, hasNextPage } = useInfiniteQuery(
    ['recipes'], 
    fetchRecipes,
    {
        getNextPageParam:  (lastPage) => lastPage.current_page == lastPage.last_page ? undefined : lastPage.current_page + 1,
    })

</script>

<template>
    <h1>Mes recettes</h1>
    <div v-if="isLoading">
        <Loader></Loader>
    </div>
    <div v-else>
        <div v-if="error">
            <p>Une erreur est survenue: {{ error.message }}</p>
        </div>
        <div v-else>
            <div v-if="data.pages.length === 0">
                <p>Vous n'avez pas encore de recettes</p>
            </div>
            <div v-else>
                <ol>
                    <template v-for="(group, index) in data.pages" :key="index">
                        <RecipeCard
                            v-for="recipe in group.data"
                            :key="recipe.id"
                            :recipe="recipe"
                        />
                    </template>
                </ol>
                <Button v-if="hasNextPage" @click="fetchNextPage" class="btn btn--secondary large"  :loading="isLoading || isFetching">Recettes suivantes</Button>
            </div>
        </div>
    </div>

</template>

<style lang="scss" scoped>
    .btn,
    h1 {
        margin-bottom: 1rem;
    }
    li {
        display: flex;
        align-items: center;
    }
    li::before {
        counter-increment: stores;
        content: counter(stores) ".";
        font-weight: 700;
        color: var(--color-primary);
        margin-right: 1rem;
    }
    ol {
        display: flex;
        flex-direction: column;
        padding-left: 0;
        counter-reset: stores;

    }
    .recipe-card {
        background: var(--color-secondary);
        font-size: 1.5rem;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    a {
        padding-right: .5rem;
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
    .checked::before {
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