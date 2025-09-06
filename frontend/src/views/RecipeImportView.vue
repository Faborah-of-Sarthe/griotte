<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import axios from 'axios'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Loader from '../components/Loader.vue'

const router = useRouter()
const recipeUrl = ref('')

// URL validation
const isValidUrl = computed(() => {
    if (!recipeUrl.value) return false
    try {
        new URL(recipeUrl.value)
        return true
    } catch {
        return false
    }
})

// Mutation pour parser l'URL
const parseRecipeMutation = useMutation({
    mutationFn: async (url) => {
        const response = await axios.post(import.meta.env.VITE_API_URL + 'recipes/parse-url', {
            url: url
        })
        return response.data
    },
    onSuccess: (data) => {
        // Naviguer vers la page de prévisualisation avec les données
        router.push({ 
            name: 'preview-imported-recipe', 
            state: { recipeData: data }
        })
    },
    onError: (error) => {
        console.error('Erreur lors du parsing:', error)
    }
})

const handleSubmit = () => {
    if (isValidUrl.value) {
        parseRecipeMutation.mutate(recipeUrl.value)
    }
}

const goBack = () => {
    router.go(-1)
}
</script>

<template>
    <div class="import-container">
        <h1>Importer une recette</h1>
        <p class="subtitle">Collez le lien d'une recette depuis votre site de cuisine préféré</p>
        
        <div class="import-form">
            <BaseInput 
                label="URL de la recette" 
                placeholder="https://www.example.com/ma-recette-preferee" 
                v-model="recipeUrl"
                type="url"
                required 
            />
            
            <div class="form-actions">
                <Button 
                    type="button" 
                    @click="handleSubmit"
                    :disabled="!isValidUrl || parseRecipeMutation.isLoading.value"
                    :loading="parseRecipeMutation.isLoading.value"
                >
                    {{ parseRecipeMutation.isLoading.value ? 'Analyse en cours...' : 'Analyser la recette' }}
                </Button>
            </div>
            
            <!-- Message d'erreur -->
            <div v-if="parseRecipeMutation.isError.value" class="error-message">
                <p>❌ Impossible d'analyser cette recette</p>
                <p class="error-details">
                    {{ parseRecipeMutation.error.value?.response?.data?.message || 'Vérifiez que l\'URL est correcte et que la page contient une recette avec des micro-données.' }}
                </p>
            </div>
            
            <!-- Loader pendant l'analyse -->
            <div v-if="parseRecipeMutation.isLoading.value" class="loading-section">
                <Loader loadingText="Analyse de la recette en cours..." />
                <p class="loading-details">
                    Griotte extrait les informations de la recette (titre, ingrédients, étapes)...
                </p>
            </div>
        </div>
        
        <div class="help-section">
            <h3>💡 Comment ça marche ?</h3>
            <ul>
                <li>Copiez l'URL complète d'une recette depuis un site de cuisine</li>
                <li>Griotte analyse automatiquement la page pour extraire les informations</li>
                <li>Vous pourrez ensuite vérifier et ajuster les données avant de créer la recette</li>
            </ul>
            
            <div class="supported-sites">
                <h4>Sites compatibles</h4>
                <p>La plupart des sites de recettes modernes utilisant des micro-données (Schema.org) sont supportés, notamment :</p>
                <p class="sites-list">Marmiton, 750g, Cuisine AZ, AlloCuisine, et bien d'autres...</p>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.import-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
}

.subtitle {
    font-size: 1.125rem;
    color: var(--color-text-secondary, #666);
    margin-bottom: 2rem;
    text-align: center;
}

.import-form {
    background: var(--color-background-soft, #f8f9fa);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    
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

.loading-section {
    margin-top: 1.5rem;
    text-align: center;
    
    .loading-details {
        margin-top: 1rem;
        font-size: 0.875rem;
        color: var(--color-text-secondary, #666);
    }
}

.help-section {
    background: var(--color-background-mute, #f3f4f6);
    border-radius: 1rem;
    padding: 1.5rem;
    
    h3 {
        margin-top: 0;
        color: var(--color-heading, #1f2937);
    }
    
    ul {
        margin: 1rem 0;
        padding-left: 1.5rem;
        
        li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }
    }
    
    .supported-sites {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--color-border, #e5e7eb);
        
        h4 {
            margin: 0 0 0.5rem 0;
            color: var(--color-heading, #1f2937);
        }
        
        p {
            margin: 0.5rem 0;
            font-size: 0.875rem;
            color: var(--color-text-secondary, #666);
            
            &.sites-list {
                font-style: italic;
            }
        }
    }
}

@media (max-width: 768px) {
    .import-container {
        padding: 1rem;
    }
    
    .import-form {
        padding: 1.5rem;
    }
}
</style>

