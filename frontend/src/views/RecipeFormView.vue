<script setup>
import { useRoute, useRouter } from 'vue-router'
import BaseInput from '../components/forms/BaseInput.vue';
import TextArea from '../components/forms/TextArea.vue';
import Button from '../components/forms/Button.vue';
import Autocomplete from '../components/forms/Autocomplete.vue';
import QuantityInput from '../components/forms/QuantityInput.vue';
import Cross from '../components/icons/Cross.vue';
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

const recipe = ref({
    name: '',
    description: '',
    link: '',
    products: []
})



const { data: recipeData } = useQuery({
    queryKey: computed(() => ['recipe', recipeId.value]),
    queryFn: () => axios.get(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value),
    enabled: computed(() => !!recipeId.value) 
})

watch(recipeData, (data) => {
    if (data) {
        recipe.value = data.data
    }
})

if (type === 'edit') {
    recipeId.value = route.params.id
}

const autocompleteUrl = computed(() => {
    return import.meta.env.VITE_API_URL + 'products/autocomplete'
})


// Recipe creation mutation
const recipeCreation = useMutation({
    mutationFn: (recipeData) => {
        return axios.post(import.meta.env.VITE_API_URL + 'recipes', recipeData)
    },
    onSuccess: (data) => {
        queryClient.invalidateQueries(['recipes'])
        // Update the recipe id to trigger the query
        recipeId.value = data.data.id
        isAddingIngredients.value = true
    }
})

// Recipe edition mutation
const recipeEdition = useMutation({
    mutationFn: (recipeData) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + route.params.id, recipeData)
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['recipes'])
        router.push({ name: 'my-recipes' })
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
const handleSubmit = () => {
    const recipeData = {
        name: recipe.value.name,
        description: recipe.value.description,
        link: recipe.value.link
    }

    if (type === 'create') {
        recipeCreation.mutate(recipeData)
    } else {
        recipeEdition.mutate(recipeData)
    }
}
</script>

<template>
    <div>
        <h1>{{ isAddingIngredients ? 'Ajouter les ingrédients' : (type === 'create' ? 'Créer une recette' : 'Modifier une recette') }}</h1>
    </div>
    
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
            required 
        />
        <BaseInput 
            label="Lien" 
            placeholder="www.recette-aux-cerises.com" 
            v-model="recipe.link" 
        />
        <Button 
            type="submit" 
            :loading="recipeCreation.isLoading.value || recipeEdition.isLoading.value"
            :disabled="!recipe.name || !recipe.description"
        >
            {{ type === 'create' ? 'Ajouter des ingrédients' : 'Modifier' }}
        </Button>
    </form>

    <!-- Interface d'ajout d'ingrédients -->
    <div v-else>
        <Autocomplete :apiUrl="autocompleteUrl.value" v-model="ingredientsSearch" @select="attachProductToRecipe" @create="attachProductToRecipe"></Autocomplete>
        
        <!-- Liste des produits -->
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
    
    <div class="save-section">
            <RouterLink :to="{ name: 'recipe', params: { id: recipeId } }" class="save-link">
                <Button design="primary">Enregistrer</Button>
            </RouterLink>
        </div>
</template>

<style lang="scss" scoped>
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
</style>
