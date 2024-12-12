<script setup>
import { useRoute } from 'vue-router'
import Card from '../components/forms/Card.vue';
import BaseInput from '../components/forms/BaseInput.vue';
import TextArea from '../components/forms/TextArea.vue';
import Button from '../components/forms/Button.vue';
import { ref } from 'vue';


const route = useRoute()
const type = route.meta.type

const recipe = ref({
    name: '',
    description: '',
})


</script>

<template>
    <div>
        <h1>{{ type === 'create' ? 'Créer une recette' : 'Modifier une recette' }}</h1>
    </div>
    <form>
        <BaseInput label="Nom" placeholder="Clafoutis aux cerises" v-model="recipe.name" />
        <TextArea label="Détails de la recette" placeholder="Une recette de clafoutis aux cerises" v-model="recipe.description" />
        <Button type="submit">{{ type === 'create' ? 'Créer' : 'Modifier' }}</Button>
    </form>
</template>

<style lang="scss" scoped>

</style>
