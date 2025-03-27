<script setup>
import { useRoute, useRouter } from 'vue-router'
import BaseInput from '../components/forms/BaseInput.vue';
import TextArea from '../components/forms/TextArea.vue';
import Button from '../components/forms/Button.vue';
import Autocomplete from '../components/forms/Autocomplete.vue';
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
})

// Au chargement on vérifie si on est en mode édition ou création
if (type === 'edit') {
    recipeId.value = route.params.id

    // Recipe get query
    const { data: recipeData } = useQuery({
        queryKey: ['recipe', recipeId.value],
        queryFn: () => axios.get(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value)
    })

    watch(recipeData, (data) => {
        recipe.value = data.data
    })

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

const attachProductQuery = useMutation({
    mutationFn: (product) => {
        return axios.post(import.meta.env.VITE_API_URL + 'recipes/' + recipeId.value + '/products', {
            product_id: product.id,
            name: product.name
        })
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['recipe', recipeId.value])
    }
})

const attachProductToRecipe = (product) => {
    attachProductQuery.mutate(product)
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
    </div>
</template>

<style lang="scss" scoped>

</style>
