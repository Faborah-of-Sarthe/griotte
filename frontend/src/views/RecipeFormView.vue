<script setup>
import { useRoute, useRouter } from 'vue-router'
import Card from '../components/forms/Card.vue';
import BaseInput from '../components/forms/BaseInput.vue';
import TextArea from '../components/forms/TextArea.vue';
import Button from '../components/forms/Button.vue';
import Autocomplete from '../components/Autocomplete.vue';
import { ref, computed } from 'vue';
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const queryClient = useQueryClient()
const type = route.meta.type

// État pour savoir si on est en mode "ajout d'ingrédients"
const isAddingIngredients = ref(false)
const recipeId = ref(null)

const recipe = ref({
    name: '',
    description: '',
    link: '',
})

// Recipe creation mutation
const recipeMutation = useMutation({
    mutationFn: (recipeData) => {
        return axios.post(import.meta.env.VITE_API_URL + 'recipes', recipeData)
    },
    onSuccess: (data) => {
        queryClient.invalidateQueries(['recipes'])
        // Au lieu de rediriger, on passe en mode ajout d'ingrédients
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

// Form submission handling
const handleSubmit = () => {
    const recipeData = {
        name: recipe.value.name,
        description: recipe.value.description,
        link: recipe.value.link
    }

    if (type === 'create') {
        recipeMutation.mutate(recipeData)
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
            :loading="recipeMutation.isLoading.value || recipeEdition.isLoading.value"
            :disabled="!recipe.name || !recipe.description"
        >
            {{ type === 'create' ? 'Créer la recette' : 'Modifier' }}
        </Button>
    </form>

    <!-- Interface d'ajout d'ingrédients -->
    <div v-else>
        <!--  TODO: Inteface d'ajout des ingrédients   -->
    </div>
</template>

<style lang="scss" scoped>

</style>
