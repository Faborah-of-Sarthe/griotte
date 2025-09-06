<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import BaseInput from '../components/forms/BaseInput.vue'
import TextArea from '../components/forms/TextArea.vue'
import Button from '../components/forms/Button.vue'
import Checkbox from '../components/forms/Checkbox.vue'
import Loader from '../components/Loader.vue'

const router = useRouter()
const queryClient = useQueryClient()

// Récupération des données depuis l'état de navigation
const recipeData = ref(null)

onMounted(() => {
    // Récupérer les données depuis l'état de l'historique
    recipeData.value = history.state?.recipeData
    
    if (!recipeData.value) {
        // Si pas de données, rediriger vers la page de choix
        router.push({ name: 'create-recipe-choice' })
        return
    }
})

// État pour les ingrédients avec possibilité de les inclure ou non
const ingredientsToInclude = ref([])

// Initialiser les ingrédients quand les données sont chargées
const initializeIngredients = () => {
    if (recipeData.value?.ingredients) {
        ingredientsToInclude.value = recipeData.value.ingredients.map((ingredient, index) => ({
            ...ingredient,
            include: true,
            uniqueId: `temp-${Date.now()}-${index}` // Identifiant unique temporaire
        }))
    }
}

// Computed pour séparer les ingrédients existants et nouveaux
const existingProducts = computed(() => {
    return ingredientsToInclude.value.filter(ingredient => ingredient.existingProduct)
})

const newProducts = computed(() => {
    return ingredientsToInclude.value.filter(ingredient => !ingredient.existingProduct)
})

// Surveiller les changements de recipeData
// Remplacer la computed par un watch
watch(recipeData, (newRecipeData) => {
    if (newRecipeData && ingredientsToInclude.value.length === 0) {
        initializeIngredients()
    }
}, { immediate: true })

// Mutation pour créer la recette
const createRecipeMutation = useMutation({
    mutationFn: async (finalRecipeData) => {
        const response = await axios.post(import.meta.env.VITE_API_URL + 'recipes/create-from-import', finalRecipeData)
        return response.data
    },
    onSuccess: (data) => {
        queryClient.invalidateQueries(['recipes'])
        router.push({ name: 'recipe', params: { id: data.id } })
    },
    onError: (error) => {
        console.error('Erreur lors de la création:', error)
    }
})

const handleCreateRecipe = () => {
    if (!recipeData.value) return
    
    const finalData = {
        name: recipeData.value.name,
        description: recipeData.value.description,
        link: recipeData.value.originalUrl,
        ingredients: ingredientsToInclude.value.filter(ing => ing.include)
    }
    
    createRecipeMutation.mutate(finalData)
}

const goBack = () => {
    router.go(-1)
}

</script>

<template>
    <div v-if="!recipeData" class="loading-container">
        <Loader loadingText="Chargement des données..." />
    </div>
    
    <div v-else class="preview-container">
        <h1>Prévisualisation de la recette</h1>
        <p class="alert-info">Vérifiez les informations extraites et ajustez si nécessaire</p>
        
        <!-- Informations de base de la recette -->
        <div class="recipe-info">
            <h2>Informations générales</h2>
            
            <BaseInput 
                label="Nom" 
                v-model="recipeData.name"
                required 
            />
            
            <TextArea 
                label="Détails de la recette" 
                v-model="recipeData.description"
                rows="6"
            />
            
            <div class="source-info alert-info">
                <p><strong>Source :</strong> {{ recipeData.originalUrl }}</p>
            </div>
        </div>
        
        <!-- Gestion des ingrédients -->
        <div class="ingredients-section">
            <h2>Ingrédients ({{ ingredientsToInclude.length }})</h2>
         
            
            <div v-if="ingredientsToInclude.length === 0" class="no-ingredients">
                <p>Aucun ingrédient n'a pu être extrait de cette recette.</p>
            </div>
            
            <div v-else class="ingredients-container">
                <p class="section-description alert-info">
                    Cochez les ingrédients que vous souhaitez inclure dans votre recette. <br>
                </p>
                <!-- Section des produits existants -->
                <div v-if="existingProducts.length > 0" class="products-subsection">
                    <h3 class="subsection-title">
                        <span class="existing-badge">Produits existants ({{ existingProducts.length }})</span>
                    </h3>
                    <div class="ingredients-list">
                        <div 
                            v-for="(ingredient, index) in existingProducts" 
                            :key="ingredient.uniqueId" 
                            class="ingredient-item"
                            :class="{ 'included': ingredient.include }"
                        >
                            <div class="ingredient-row">
                                <!-- Nom de l'ingrédient - non modifiable pour les existants -->
                                <div class="ingredient-name-section">
                                    <div class="ingredient-name-display">
                                        {{ ingredient.name }}
                                    </div>
                                </div>
                                <div class="ingredient-actions">
                                    <!-- Quantité - toujours modifiable -->
                                    <div class="ingredient-quantity-section">
                                        <BaseInput
                                            v-model="ingredient.quantity"
                                            placeholder="Quantité"
                                            class="ingredient-quantity-input"
                                        />
                                    </div>
                                    
                                    <!-- Checkbox pour inclure dans la recette -->
                                    <div class="ingredient-include-section">
                                        <Checkbox
                                            hideLabel
                                            :label="ingredient.uniqueId"
                                            :modelValue="ingredient.include"
                                            @update:modelValue="ingredient.include = $event"
                                            type="option"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section des nouveaux produits -->
                <div v-if="newProducts.length > 0" class="products-subsection">
                    <h3 class="subsection-title">
                        <span class="new-badge">Nouveaux produits ({{ newProducts.length }})</span>
                    </h3>
                    <p class="section-description alert-info">Ces produits ne sont pas encore dans votre liste. Si vous les incluez, ils seront automatiquement ajoutés à votre liste.</p>
                    <div class="ingredients-list">
                        <div 
                            v-for="(ingredient, index) in newProducts" 
                            :key="ingredient.uniqueId" 
                            class="ingredient-item"
                            :class="{ 'included': ingredient.include }"
                        >
                            <div class="ingredient-row">
                                <!-- Nom de l'ingrédient - modifiable pour les nouveaux -->
                                <div class="ingredient-name-section">
                                    <BaseInput
                                        v-model="ingredient.name"
                                        placeholder="Nom de l'ingrédient"
                                        class="ingredient-name-input"
                                    />
                                </div>
                                <div class="ingredient-actions">
                                    <div class="ingredient-quantity-section">
                                        <BaseInput
                                        v-model="ingredient.quantity"
                                        placeholder="Quantité"
                                        class="ingredient-quantity-input"
                                        />
                                    </div>
                                    <div class="ingredient-include-section">
                                        <Checkbox
                                        hideLabel
                                        :label="ingredient.uniqueId"
                                        :modelValue="ingredient.include"
                                        @update:modelValue="ingredient.include = $event"
                                        type="option"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="form-actions">
            <Button 
            type="button" 
            @click="handleCreateRecipe"
            :disabled="!recipeData.name || createRecipeMutation.isLoading.value"
            :loading="createRecipeMutation.isLoading.value"
            >
            {{ createRecipeMutation.isLoading.value ? 'Création en cours...' : 'Créer la recette' }}
            </Button>
            <Button 
                design="secondary" 
                type="button" 
                @click="goBack"
                :disabled="createRecipeMutation.isLoading.value"
            >
                Retour
            </Button>
        </div>
        
        <!-- Message d'erreur -->
        <div v-if="createRecipeMutation.isError.value" class="error-message">
            <p>❌ Erreur lors de la création de la recette</p>
            <p class="error-details">
                {{ createRecipeMutation.error.value?.response?.data?.message || 'Une erreur inattendue s\'est produite.' }}
            </p>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 50vh;
}
h2 {
    color: var(--color-text-alt);
}
h3 {
    color: var(--color-text-alt);
    font-weight: bold;
    margin-bottom: 0.5rem;

}
.preview-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.subtitle {
    font-size: 1.125rem;
    color: var(--color-text-secondary, #666);
    margin-bottom: 2rem;
    text-align: center;
}



.source-info {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--color-background-mute, #f3f4f6);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    
    p {
        margin: 0;
        word-break: break-all;
    }
}



.no-ingredients {
    text-align: center;
    padding: 2rem;
    color: var(--color-text-secondary, #666);
}

.ingredients-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}



.ingredients-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}



.ingredient-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    gap: 0.5rem;

    :deep(input) {
        margin: 0;
    }
}

.ingredient-actions {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    
    :deep(label) {
        margin: 0;
    }
}

.ingredient-name-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
    
    .ingredient-name-input {
        width: 100%;
    }
    
    .ingredient-name-display {
        font-weight: 500;
        padding: 0.75rem;
        background: var(--color-background-mute, #f3f4f6);
        border-radius: 0.375rem;
        color: var(--color-text-secondary, #666);
    }
}

.ingredient-quantity-section {
    width: 5rem;

    :deep(input) {
        width: 100%;
        font-size: 0.8rem;
        margin: 0;
    }
}

.ingredient-include-section {
    display: flex;
    align-items: center;
    justify-content: center;
}






.create-product-option {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--color-border, #e5e7eb);
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    
    @media (max-width: 768px) {
        flex-direction: column;
        
        :deep(.btn) {
            width: 100%;
        }
    }
}

.error-message {
    margin-top: 1.5rem;
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.5rem;
    color: #dc2626;
    
    p {
        margin: 0;
        
        &.error-details {
            font-size: 0.875rem;
            margin-top: 0.5rem;
            opacity: 0.8;
        }
    }
}

@media (max-width: 768px) {
    .preview-container {
        padding: 1rem;
    }
    


    .ingredient-include-section {
        padding-top: 0;
        justify-content: flex-start;
    }
}
</style>

