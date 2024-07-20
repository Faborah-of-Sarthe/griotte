<script setup>
import { useInfiniteQuery } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import Button from '@/components/forms/Button.vue'
import RecipeCard from '@/components/RecipeCard.vue'



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
                <ul>
                    <template v-for="(group, index) in data.pages" :key="index">
                        <li v-for="(recipe, recipeIndex) in group.data" :key="recipe.id">
                            <RecipeCard :recipe="recipe" ></RecipeCard>
                        </li>
                    </template>
                </ul>
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
</style>