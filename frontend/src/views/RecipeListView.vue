<script setup>
import { useInfiniteQuery } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import Button from '@/components/forms/Button.vue'
import RecipeCard from '../components/RecipeCard.vue'
import { useUserStore } from '../stores/user'
import CheckMark from '@/components/icons/CheckMark.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import { computed, watch } from 'vue'
import { useDebouncedRef } from '../utils'
import { useQueryClient } from '@tanstack/vue-query'


const userStore = useUserStore()

const recipeChoice = computed(() => userStore.getRecipeChoice)
const search =  useDebouncedRef('', 400)

const queryClient = useQueryClient()
const fetchRecipes = async ({pageParam = 1}) => {
    const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes', {
      params: {
        page: pageParam,
        choice: recipeChoice.value,
        search: search.value
      }
    })

    return res.data
}

// Watch for the search terms and emit the event
watch(search, (value) => {
    if (value.length > 2 || value.length === 0) {
        queryClient.invalidateQueries(['recipes', recipeChoice])
    }
})


const { data, error, fetchNextPage, isLoading,isFetching, hasNextPage } = useInfiniteQuery(
    ['recipes', recipeChoice], 
    fetchRecipes,
    {
        getNextPageParam:  (lastPage) => lastPage.current_page == lastPage.last_page ? undefined : lastPage.current_page + 1,
    })

</script>

<template>
    <h1>Mes recettes</h1>
    <div class="controls">
        <div class="buttons">
            <Button :class="{'btn--secondary': recipeChoice !== 'all'}" @click="userStore.setRecipeChoice('all')">Toutes</Button>
            <Button :class="{'btn--secondary': recipeChoice !== 'to_make'}" @click="userStore.setRecipeChoice('to_make')">
                <CheckMark class="checked"/>
                À faire
            </Button>
        </div>
        <div class="search">
            <BaseInput v-model="search" placeholder="Rechercher une recette" />
        </div>
    </div>
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
    a {
        padding-right: .5rem;
    }
    .checked::before {
        color: var(--color-background);
    }
    .controls {

        .btn--secondary {
            border: none;
            box-shadow: inset 0 0 0 2px var(--color-primary);
        }
 
        .checked {
            stroke: var(--color-background);
            border: 2px solid var(--color-background);
        }
        .btn--secondary .checked {
            stroke: var(--color-primary);
            border: 2px solid var(--color-primary);
        }
        .checkmark {
            margin-right: 1rem;
            margin-left: 0;
        }
        
        .checked .checkbox {
            stroke: var(--color-background);
            border: 2px solid var(--color-background);
            
        }
    }
    .controls .buttons {
        display: flex;
        gap: 1rem;
    }
    .controls button {
        flex: 1;
    }
</style>