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
import { useQueryClient, useQuery } from '@tanstack/vue-query'
import Cross from '@/components/icons/Cross.vue'


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

const fetchNumberOfRecipesToMake = async () => {
    const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes/count', {
      
    })
    return res.data
}

const { data: numberOfRecipesToMake, isLoading: isLoadingNumberOfRecipesToMake } = useQuery({
    queryKey: ['numberOfRecipesToMake'],
    queryFn: fetchNumberOfRecipesToMake
})

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
    <div class="header">
        <h1>Mes recettes</h1>
        <button class="add_button"> <Cross class="small plus" /></button>
    </div>
    <div class="controls">
        <div class="buttons">
            <Button :class="{'btn--secondary': recipeChoice !== 'all'}" @click="userStore.setRecipeChoice('all')">Toutes</Button>
            <Button :class="{'btn--secondary': recipeChoice !== 'to_make'}" @click="userStore.setRecipeChoice('to_make')">
                À faire  
                <span v-if="isLoadingNumberOfRecipesToMake" class="custom-loader"> </span>
                <span class="number" v-else>{{ numberOfRecipesToMake }}</span>
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
            <div v-if="data.pages[0].data.length === 0 && recipeChoice === 'all'">
                <div class="alert-info">Vous n'avez pas encore de recettes</div>
            </div>
            <div v-else-if="data.pages[0].data.length === 0 && recipeChoice === 'to_make'">
                <div class="alert-info">Vous n'avez pas encore de recettes à faire</div>
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
    .header {
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
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .add_button {
        background: var(--color-primary);
        border: none;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;    
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        outline: none;

    }
    .add_button svg {
        fill: var(--color-background);
        width: 1rem;
        height: 1rem;
        transform: rotate(45deg);
    }

    .plus {
        height: .9rem;
        width: .9rem;
        transform: rotate(45deg);

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
    .controls .custom-loader {
        margin-left: .5rem;

    }
    .number {
        font-size: .9rem;
        margin-left: .5rem;

        background-color: var(--color-background);
        color: var(--color-primary);
        width: 1.3rem;
        height: 1.3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn--secondary .number {
        background-color: var(--color-primary);
        color: var(--color-background);
    }
</style>