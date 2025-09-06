<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useQuery, useMutation } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import Button from '../components/forms/Button.vue'
import Cross from '../components/icons/Cross.vue'
import ExternalLink from '../components/icons/ExternalLink.vue'
import TodoButton from '../components/TodoButton.vue'
import Modal from '../components/Modal.vue'
import { computed, ref } from 'vue'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const recipeId = computed(() => route.params.id)

// Set to track which ingredients are being added
const addingIngredients = ref(new Set())

// Modal state for recipe deletion
const openDeleteModal = ref(false)

const { data: recipe, isLoading, isError, error } = useQuery({
    queryKey: computed(() => ['recipe', recipeId.value]),
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value)
        return res.data
    },
    enabled: computed(() => !!recipeId.value)
})

const {mutate: addIngredient} = useMutation({
    mutationFn: async (ingredient) => {
        const response = await axios.post(
            `${import.meta.env.VITE_API_URL}recipes/${recipeId.value}/products/${ingredient.id}/add-to-list`
        )
        return response.data
    },
    onMutate: (ingredient) => {
        addingIngredients.value.add(ingredient.id)
    },
    onSettled: (data, error, ingredient) => {
        addingIngredients.value.delete(ingredient.id)
    },
    onError: (error) => {
        console.error('Erreur lors de l\'ajout de l\'ingrédient à la liste:', error)
    }
})

const {isLoading: isAddingAllIngredients, mutate: addAllIngredients} = useMutation({
    mutationFn: async () => {
        const response = await axios.post(
            `${import.meta.env.VITE_API_URL}recipes/${recipeId.value}/add-all-to-list`
        ) 
        return response.data
    },
    onError: (error) => {
        console.error('Erreur lors de l\'ajout des ingrédients à la liste:', error)
    }
})

const {mutate: deleteRecipeMutation} = useMutation({
    mutationFn: async () => {
        const response = await axios.delete(
            `${import.meta.env.VITE_API_URL}recipes/${recipeId.value}`,
            { withCredentials: true }
        )
        return response.data
    },
    onSuccess: () => {
        router.push({ name: 'my-recipes' })
    },
    onError: (error) => {
        console.error('Erreur lors de la suppression de la recette:', error)
    }
})



const addIngredientToList = (ingredient) => {
    addIngredient(ingredient)
}

const addAllIngredientsToList = () => {
    addAllIngredients()
}

const editRecipe = () => {
    router.push({ name: 'edit-recipe', params: { id: recipeId.value } })
}

const deleteRecipe = () => {
    openDeleteModal.value = true
}

const handleDeleteRecipe = () => {
    deleteRecipeMutation()
    openDeleteModal.value = false
}


</script>

<template>
    <div>
        <div v-if="isLoading">
            <Loader loadingText="Chargement de la recette..." />
        </div>
        
        <div v-else-if="isError">
            <div class="error">
                <p>Une erreur est survenue lors du chargement de la recette</p>
                <p v-if="error">{{ error.message }}</p>
            </div>
        </div>
        
        <div v-else-if="recipe" class="recipe-detail">
            <div class="header">
                <h1>
                    {{ recipe.name }}
                    <a v-if="recipe.link" 
                       :href="recipe.link" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="external-link-icon"
                       :title="`Ouvrir le lien de la recette dans un nouvel onglet`">
                        <ExternalLink />
                    </a>
                </h1>
            </div>
            
            <div class="ingredients">
                <div class="ingredients-header">
                    <h2>Ingrédients</h2>
                    
                </div>
                
                <div v-if="!recipe.products || recipe.products.length === 0" class="no-ingredients">
                    <p class="alert-info">Aucun ingrédient n'a été ajouté à cette recette.</p>
                </div>
                <div v-else>
                    <ul class="ingredients-list">
                        <li v-for="product in recipe.products" :key="product.id" class="ingredient-item">
                            <span class="ingredient-name">
                                {{ product.name }}
                                <span v-if="product.pivot && product.pivot.quantity" class="quantity">
                                    ({{ product.pivot.quantity }})
                                </span>
                            </span>
                            <Button 
                            design="secondary" 
                            @click="addIngredientToList(product)" 
                            type="button"
                            class="add-button"
                            :disabled="addingIngredients.has(product.id)"
                            >
                                <Cross class="plus-icon" />
                            </Button>
                        </li>
                    </ul>
                    <div class="buttons">
                        <Button 
                            v-if="recipe.products && recipe.products.length > 0"
                            design="secondary" 
                            @click="addAllIngredientsToList" 
                            type="button"
                            class="btn small add-all-ingredients"
                            :disabled="isAddingAllIngredients"
                            >
                            <Cross class="plus-icon" />
                            {{ isAddingAllIngredients ? 'Ajout en cours...' : 'Tout ajouter à ma liste' }}
                        </Button>
                    </div>
                </div>
            </div>

            <div v-if="recipe.description" class="description">
                <h2>Description</h2>
                <p>{{ recipe.description }}</p>
            </div>
         
            
            <div class="action-buttons">
                <Button design="secondary" @click="deleteRecipe" type="button">
                    Supprimer
                </Button>
                <Button design="secondary" @click="editRecipe" type="button">
                    Modifier
                </Button>
                <TodoButton :recipe="recipe" :show-text="true" :as-button="true" />
            </div>
            <Button design="secondary" @click="router.push({ name: 'my-recipes' })" type="button" class="go-back-button ">
                Retour aux recettes
            </Button>
        </div>
        
        <!-- Modal de confirmation de suppression -->
        <Modal v-if="openDeleteModal" @close="openDeleteModal = false" title="Suppression" :buttons="true" @validate="handleDeleteRecipe">
            <template #content>
                <p>Voulez-vous vraiment supprimer cette recette ?</p>
                <p>Attention, cette action est définitive !</p>
            </template>
        </Modal>
    </div>
</template>

<style lang="scss" scoped>
.recipe-detail {
    padding: 1rem;
}
h2 {
    color: var(--color-text-alt);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
    
    h1 {
        margin: 0;
        flex: 1;
        min-width: 250px;
        display: flex;
        align-items: baseline;
        gap: 0.75rem;
    }
    
    .external-link-icon {
        color: var(--color-primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        
        svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        &:hover {
            opacity: 0.8;
        }
    }
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    :deep(.btn) {
        font-size: 1rem;
    }
}



.description {
    h2 {
        margin-bottom: 1rem;
    }
    margin-bottom: 2rem;
    white-space: pre-wrap;
    p {
        line-height: 1.6;
        margin: 0;
    }
}

.buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
    margin-bottom: 2rem;

    :deep(.btn) {
        margin: 0;
    }
}

.recipe-link {
    margin-bottom: 2rem;
    
   
    
    a {
        color: var(--color-primary);
        text-decoration: underline;
        word-break: break-all;
    }
}

.ingredients {
    
    .ingredients-list {
        padding: 0;
        margin: 0;
        padding-top: 1rem;
        
        .ingredient-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            
            .ingredient-name {
                flex: 1;
                font-size: 1.1rem;
                
                .quantity {
                    color: var(--color-text-alt);
                }
            }
            
            .add-button {
                min-width: auto;
                width: 1.5rem;
                height: 1.5rem;
                border-radius: 50%;
                padding: 0;
                display: flex;
                align-items: center;
                border: 0;
                justify-content: center;
                box-shadow: none;
                
            }
            .add-button:disabled {
                background: var(--color-background);
                color: var(--color-text-alt);
            }
        }
    }
    .buttons {
        .plus-icon {
            margin-right: 0.5rem;
        }
    }
    .plus-icon {
        width: .8rem;
        height: .8rem;
        transform: rotate(45deg);
    }
}
.add-all-ingredients:disabled {
    background: var(--color-background);
    color: var(--color-text-alt);
    border: 1px solid var(--color-text-alt);
}

.error {
    text-align: center;
    padding: 2rem;
    color: var(--color-danger);
    
    p {
        margin: 0.5rem 0;
    }
}
.go-back-button {
        margin-top: 2rem;
    }
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        
        .action-buttons {
            width: 100%;
            
            :deep(.btn) {
                flex: 1;
            }
        }
    }
    
    .ingredients-header {
        flex-direction: column;
        align-items: flex-start;
        
        :deep(.btn) {
            width: 100%;
        }
    }
 
}
</style>
