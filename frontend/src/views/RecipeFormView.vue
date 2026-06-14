<script setup>
import { useRoute, useRouter } from 'vue-router'
import BaseInput from '../components/forms/BaseInput.vue';
import TextArea from '../components/forms/TextArea.vue';
import Button from '../components/forms/Button.vue';
import Autocomplete from '../components/forms/Autocomplete.vue';
import QuantityInput from '../components/forms/QuantityInput.vue';
import Cross from '../components/icons/Cross.vue';
import Loader from '../components/Loader.vue';
import { ref, computed, watch } from 'vue';
import { useMutation, useQueryClient, useQuery } from '@tanstack/vue-query'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const queryClient = useQueryClient()
const type = route.meta.type

// État pour savoir si on est en mode "ajout d'ingrédients"
const isAddingIngredients = ref(false)
const recipeId = ref(null)
const ingredientsSearch = ref('')
const selectedTags = ref([])

const recipe = ref({
    name: '',
    description: '',
    link: '',
    products: [],
    tags: []
})



const { data: recipeData, isLoading: isLoadingRecipe, error: recipeError } = useQuery({
    queryKey: computed(() => ['recipe', recipeId.value]),
    queryFn: () => axios.get(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value),
    enabled: computed(() => !!recipeId.value) 
})

watch(recipeData, (data) => {
    if (data) {
        recipe.value.name = data.data?.name
        recipe.value.description = data.data?.description
        recipe.value.link = data.data?.link
        recipe.value.products = data.data?.products
        recipe.value.tags = data.data?.tags
        selectedTags.value = data.data?.tags?.map((tag) => tag.id) ?? []
    }
})

if (type === 'edit') {
    recipeId.value = route.params.id
}

const autocompleteUrl = computed(() => {
    return import.meta.env.VITE_API_URL + 'products/autocomplete'
})

const { data: tags } = useQuery({
    queryKey: ['tags'],
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL + 'tags')
        return res.data
    }
})

const syncRecipeTags = async (id) => {
    await axios.put(import.meta.env.VITE_API_URL + 'recipes/' + id + '/tags', {
        tag_ids: selectedTags.value
    })
}

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


// Recipe creation mutation
const recipeCreation = useMutation({
    mutationFn: (recipeData) => {
        return axios.post(import.meta.env.VITE_API_URL + 'recipes', recipeData)
    },
    onSuccess: async (data) => {
        // Update the recipe id to trigger the query
        recipeId.value = data.data.id
        await syncRecipeTags(recipeId.value)
        queryClient.invalidateQueries(['recipes'])
        queryClient.invalidateQueries(['recipe', recipeId.value])
        isAddingIngredients.value = true
    }
})


const recipeEdition = useMutation({
    mutationFn: (recipeData) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + route.params.id, recipeData)
    },
    onSuccess: async () => {
        await syncRecipeTags(route.params.id)
        queryClient.invalidateQueries(['recipes'])
        queryClient.invalidateQueries(['recipe', recipeId.value])
        isAddingIngredients.value = true
    }
})

const recipeEditionAndReturn = useMutation({
    mutationFn: (recipeData) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + route.params.id, recipeData)
    },
    onSuccess: async () => {
        await syncRecipeTags(route.params.id)
        // Rafraîchir les données plutôt que juste invalider
        await queryClient.refetchQueries(['recipes'])
        await queryClient.refetchQueries(['recipe', recipeId.value])
        
        // Naviguer vers la page de détail
        router.push({ name: 'recipe', params: { id: recipeId.value } })
    }
})

const attachProductQuery = useMutation({
    mutationFn: (product) => {
        return axios.post(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value + '/products', {
            product_id: product.id,
            name: product.name
        })
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['recipe', recipeId.value])
        ingredientsSearch.value = ''
    }
})

const detachProductQuery = useMutation({
    mutationFn: (productId) => {
        return axios.delete(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value + '/products/' + productId)
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['recipe', recipeId.value])
    }
})



const attachProductToRecipe = (product) => {
    attachProductQuery.mutate(product)
}

const detachProductFromRecipe = (productId) => {
    detachProductQuery.mutate(productId)
}



// Form submission handling
const handleSubmit = (action = 'ingredients') => {
    const recipeData = {
        name: recipe.value.name,
        description: recipe.value.description,
        link: recipe.value.link
    }

    if (type === 'create') {
        recipeCreation.mutate(recipeData)
    } else {
        if (action === 'save') {
            recipeEditionAndReturn.mutate(recipeData)
        } else {
            recipeEdition.mutate(recipeData)
        }
    }
}
</script>

<template>
    <!-- État de chargement pour l'édition -->
    <Loader v-if="type === 'edit' && isLoadingRecipe" loadingText="Chargement de la recette..." />
    
    <!-- Erreur de chargement -->
    <div v-else-if="type === 'edit' && recipeError" class="error-message">
        <p>Erreur lors du chargement de la recette</p>
        <Button @click="router.go(-1)">Retour</Button>
    </div>
    
    <!-- Contenu principal -->
    <div v-else-if="type === 'create' || (type === 'edit' && !isLoadingRecipe && !recipeError)">
        <h1>{{ isAddingIngredients ? 'Ajouter les ingrédients' : (type === 'create' ? 'Créer une recette' : 'Modifier une recette') }}</h1>
    
        <!-- Formulaire initial -->
    <form v-if="!isAddingIngredients" @submit.prevent="handleSubmit">
        <BaseInput 
            label="Nom" 
            placeholder="Clafoutis aux cerises" 
            v-model="recipe.name"
            required 
        />
        <TextArea 
            label="Détails de la recette" 
            placeholder="Une recette de clafoutis aux cerises" 
            v-model="recipe.description"
        />
        <p class="markdown-help">
            Mise en forme markdown supportée : **gras**, *italique*, # titres, - listes, [liens](https://exemple.com).
        </p>
        <BaseInput 
            label="Lien" 
            placeholder="www.recette-aux-cerises.com" 
            v-model="recipe.link" 
        />
        <div v-if="tags && tags.length > 0" class="recipe-tags-field">
            <label>Tags</label>
            <div class="tag-buttons">
                <button
                    v-for="tag in tags"
                    :key="tag.id"
                    type="button"
                    class="tag-select"
                    :class="{ selected: isTagSelected(tag.id) }"
                    @click="toggleTag(tag.id)"
                >
                    {{ tag.name }}
                </button>
            </div>
        </div>
        <p v-else class="markdown-help">
            Aucun tag disponible pour le moment.
            <RouterLink :to="{ name: 'recipe-tags' }">Créer des tags</RouterLink>
        </p>
        <!-- Boutons pour la création -->
        <Button 
            v-if="type === 'create'"
            type="submit" 
            :loading="recipeCreation.isLoading.value"
            :disabled="!recipe.name"
        >
            {{ recipeCreation.isLoading.value ? 'Création en cours...' : 'Ajouter des ingrédients' }}
        </Button>
        
        <div v-else class="edit-buttons">
            <Button 
                type="button"
                design="secondary"
                @click="handleSubmit('save')"
                :loading="recipeEditionAndReturn.isLoading.value"
                :disabled="!recipe.name"
            >
                {{ recipeEditionAndReturn.isLoading.value ? 'Enregistrement...' : 'Enregistrer' }}
            </Button>
            <Button 
                type="submit"
                @click="handleSubmit('ingredients')"
                :loading="recipeEdition.isLoading.value"
                :disabled="!recipe.name"
            >
                {{ recipeEdition.isLoading.value ? 'Modification en cours...' : 'Gérer les ingrédients' }}
            </Button>
        </div>
    </form>

    <!-- Interface d'ajout d'ingrédients -->
    <div v-else>
        <Autocomplete 
            :apiUrl="autocompleteUrl.value" 
            v-model="ingredientsSearch" 
            @select="attachProductToRecipe" 
            @create="attachProductToRecipe"
            :disabled="attachProductQuery.isLoading.value"
        ></Autocomplete>
        
        <div v-if="attachProductQuery.isLoading.value" class="loading-message">
            <Loader inline loadingText="Ajout de l'ingrédient..." />
        </div>
        
        <div v-if="recipe.products.length > 0" class="products-list">
            <ul>
                <li v-for="product in recipe.products" :key="product.id" class="product-item">
                    <span>{{ product.name }}</span>
                    <span class="product-item-actions">
                        <QuantityInput 
                            :quantity="product.pivot.quantity" 
                            placeholder="Quantité"
                            :recipe-id="recipeId"
                            :product-id="product.id"
                        />
                        <button class="delete-button" @click="detachProductFromRecipe(product.id)" :disabled="detachProductQuery.isLoading.value">
                            <span class="sr-only">Supprimer</span>
                            <Cross />
                        </button>
                    </span>
                </li>
            </ul>
        </div>
        <div v-else>
            <p class="alert-info">Aucun ingrédient pour l'instant</p>
        </div>
    </div>
    
        <div v-if="isAddingIngredients" class="save-section">
                <RouterLink :to="{ name: 'recipe', params: { id: recipeId } }" class="save-link">
                    <Button design="primary">Enregistrer</Button>
                </RouterLink>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.error-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    text-align: center;
    color: var(--color-danger, #ef4444);
    
    p {
        font-size: 1.125rem;
        margin: 0;
    }
}

.loading-message {
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 0.5rem;
    background-color: var(--color-background-soft, #f8f9fa);
    display: flex;
    justify-content: center;
}

.markdown-help {
    color: var(--color-text-alt);
    font-size: 0.9rem;
    margin: -0.5rem 0 1rem;
}

.products-list {
    margin-top: 2rem;

    ul {
        padding: 0;
    }

    .product-item {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.25rem;

        .product-item-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }



        .delete-button {
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text);
            width: 2rem;
            height: 2rem;

            &:disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    }
}

.edit-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
    
    @media (max-width: 768px) {
        flex-direction: column;
        
        :deep(.btn) {
            width: 100%;
        }
    }
}

.recipe-tags-field {
    margin-bottom: 1rem;
}

.tag-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag-select {
    align-items: center;
    background: var(--color-1-light);
    border: 1px solid transparent;
    border-radius: 0.375rem;
    color: var(--color-primary);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 1.2;
    padding: 0.35rem 0.65rem;
    transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

.tag-select.selected {
    background: var(--color-primary);
    color: var(--color-background);
}
</style>
