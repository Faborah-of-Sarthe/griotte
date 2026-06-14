<script setup>
import { useInfiniteQuery } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import Button from '@/components/forms/Button.vue'
import RecipeCard from '../components/RecipeCard.vue'
import { useUserStore } from '../stores/user'
import CheckMark from '@/components/icons/CheckMark.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import { computed, ref, watch } from 'vue'
import { useDebouncedRef } from '../utils'
import { useQueryClient, useQuery } from '@tanstack/vue-query'
import Cross from '@/components/icons/Cross.vue'


const userStore = useUserStore()

const recipeChoice = computed(() => userStore.getRecipeChoice)
const search =  useDebouncedRef('', 400)
const selectedTags = ref([])
const showTagFilters = ref(false)
const hasActiveFilters = computed(() => search.value.length > 0 || selectedTags.value.length > 0)

const queryClient = useQueryClient()
const recipesQueryKey = computed(() => ['recipes', recipeChoice.value, selectedTags.value.join(',')])
const fetchRecipes = async ({pageParam = 1}) => {
    const params = {
        page: pageParam,
        choice: recipeChoice.value,
        search: search.value
    }

    if (selectedTags.value.length > 0) {
        params.tags = selectedTags.value
    }

    const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes', {
      params
    })

    return res.data
}

const fetchNumberOfRecipesToMake = async () => {
    const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes/count', {
      
    })
    return res.data
}

const fetchTags = async () => {
    const res = await axios.get(import.meta.env.VITE_API_URL + 'tags')
    return res.data
}

const { data: numberOfRecipesToMake, isLoading: isLoadingNumberOfRecipesToMake } = useQuery({
    queryKey: ['numberOfRecipesToMake'],
    queryFn: fetchNumberOfRecipesToMake
})

const { data: tags, isLoading: isLoadingTags } = useQuery({
    queryKey: ['tags'],
    queryFn: fetchTags
})

// Watch for the search terms and emit the event
watch(search, (value) => {
    if (value.length > 2 || value.length === 0) {
        queryClient.invalidateQueries(['recipes'])
    }
})

const toggleTag = (tagId) => {
    if (selectedTags.value.includes(tagId)) {
        selectedTags.value = selectedTags.value.filter((selectedTagId) => selectedTagId !== tagId)
    } else {
        selectedTags.value = [...selectedTags.value, tagId]
    }
}

const isTagSelected = (tagId) => {
    return selectedTags.value.includes(tagId)
}

const clearSelectedTags = () => {
    selectedTags.value = []
}


const { data, error, fetchNextPage, isLoading,isFetching, hasNextPage } = useInfiniteQuery(
    recipesQueryKey, 
    fetchRecipes,
    {
        getNextPageParam:  (lastPage) => lastPage.current_page == lastPage.last_page ? undefined : lastPage.current_page + 1,
    })

</script>

<template>
    <div class="header">
        <h1>Mes recettes</h1>
    </div>
    <div class="controls">
        <div class="buttons">
            <Button :design="recipeChoice !== 'all' ? 'secondary' : 'primary'" @click="userStore.setRecipeChoice('all')">Toutes</Button>
            <Button :design="recipeChoice !== 'to_make' ? 'secondary' : 'primary'" @click="userStore.setRecipeChoice('to_make')">
                À faire  
                <span v-if="isLoadingNumberOfRecipesToMake" class="custom-loader"> </span>
                <span class="number" v-else>{{ numberOfRecipesToMake }}</span>
            </Button>
        </div>
        <div class="search">
            <BaseInput v-model="search" placeholder="Rechercher une recette" />
        </div>
        <div class="tag-filters" v-if="!isLoadingTags && tags">
            <button
                type="button"
                class="tag-filters-toggle"
                :aria-expanded="showTagFilters"
                @click="showTagFilters = !showTagFilters"
            >
                Filtrer par tags
                <span v-if="selectedTags.length > 0" class="tag-filters-count">{{ selectedTags.length }}</span>
                <span class="tag-filters-chevron" :class="{ open: showTagFilters }" aria-hidden="true"></span>
            </button>
            <div v-if="showTagFilters" class="tag-buttons">
                <button
                    v-for="tag in tags"
                    :key="tag.id"
                    type="button"
                    class="tag-filter"
                    :class="{ selected: isTagSelected(tag.id) }"
                    @click="toggleTag(tag.id)"
                >
                    {{ tag.name }}
                </button>
                <button
                    v-if="selectedTags.length > 0"
                    type="button"
                    class="tag-filter clear"
                    @click="clearSelectedTags"
                >
                    Réinitialiser
                </button>
                <RouterLink :to="{ name: 'recipe-tags' }" class="tag-manage-link">
                    Gérer les tags
                </RouterLink>
            </div>
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
            <div v-if="data.pages[0].data.length === 0 && hasActiveFilters">
                <div class="alert-info">Aucune recette ne correspond aux filtres sélectionnés</div>
            </div>
            <div v-else-if="data.pages[0].data.length === 0 && recipeChoice === 'all'">
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
                <Button v-if="hasNextPage" @click="fetchNextPage" class="btn btn--secondary large next-button"  :loading="isLoading || isFetching">Recettes suivantes</Button>
            </div>
        </div>
    </div>
    <div class="bottom-controls">
        <Transition name="slideUp" appear>
            <RouterLink :to="{ name: 'create-recipe-choice' }">
                <button class="add_button"> <Cross class="small plus" /></button>
            </RouterLink>
        </Transition>
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
    .bottom-controls {
        position: fixed;
        bottom:0;
        left:0;
        right:0;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
        padding-bottom: 2rem;
        pointer-events: none;

        > * {
            pointer-events: all;
        }
    }
    .next-button {
        margin-bottom: 5em;
    }
    .add_button {
        background: var(--color-primary);
        border: none;
        border-radius: 50%;
        width: 4rem;
        height: 4rem;
        display: flex;
        align-items: center;    
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        outline: none;

    }
    .add_button svg {
        fill: var(--color-background);
        width: 1.5rem;
        height: 1.5rem;
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
        color: var(--color-primary);
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
    .controls .buttons button {
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

    .btn--primary:hover {
        box-shadow: inset 0 0 0 2px var(--color-primary);
    }

    .tag-filters {
        margin: 1rem 0 1.5rem;
    }

    .tag-filters-toggle {
        align-items: center;
        background: none;
        border: none;
        color: var(--color-primary);
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: 1rem;
        font-weight: 700;
        gap: 0.5rem;
        padding: 0;
    }

    .tag-filters-count {
        align-items: center;
        background: var(--color-primary);
        border-radius: 0.375rem;
        color: var(--color-background);
        display: inline-flex;
        font-size: 0.8rem;
        height: 1.25rem;
        justify-content: center;
        min-width: 1.25rem;
        padding: 0 0.35rem;
    }

    .tag-filters-chevron {
        border-bottom: 2px solid currentColor;
        border-right: 2px solid currentColor;
        display: inline-block;
        height: 0.45rem;
        margin-top: -0.2rem;
        transform: rotate(45deg);
        transition: transform 0.2s ease-in-out, margin-top 0.2s ease-in-out;
        width: 0.45rem;
    }

    .tag-filters-chevron.open {
        margin-top: 0.2rem;
        transform: rotate(225deg);
    }

    .tag-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .tag-filter {
        align-items: center;
        background: var(--color-1-light);
        border: 1px solid transparent;
        border-radius: 0.375rem;
        color: var(--color-primary);
        cursor: pointer;
        display: inline-flex;
        flex: 0 0 auto;
        font-family: inherit;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.2;
        padding: 0.35rem 0.65rem;
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }

    .tag-filter.selected {
        background: var(--color-primary);
        color: var(--color-background);
    }

    .tag-filter.clear {
        background: var(--color-background);
        border-color: var(--color-text-alt);
        color: var(--color-text-alt);
    }

    .tag-manage-link {
        align-items: center;
        background: var(--color-background);
        border: 1px dashed var(--color-primary);
        border-radius: 0.375rem;
        color: var(--color-primary);
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.2;
        padding: 0.35rem 0.65rem;
    }

</style>