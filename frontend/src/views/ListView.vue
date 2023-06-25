<script setup>

import { useQuery } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
import axios from 'axios'
import { ref } from 'vue'


const hasProducts = ref(false);


// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'products', {
      withCredentials: true,

    })
    hasProducts.value = res.data.some((section) => section.products.length > 0)
    return res.data

  },
})


</script>
<template>
  <h1>Ma liste</h1>
  <div class="sections">
    <div v-if="isLoading">Loading...</div>
    <div v-if="isError">Error: {{ error }}</div>
    <div v-if="data">
      <!-- Loop through example array with animation -->
      <template v-for="section in data" :key="section.id">
          <Transition name="slideIn" appear>
            <Section :style="{ 'transition-delay': section.id * 50 + 'ms' }" :section="section" v-if="section.products.length > 0"></Section>
          </Transition>
      </template>
      <p v-if="!hasProducts">
        Il n'y a pas encore de produits dans votre liste. <br>Utilisez le bouton ci-dessous pour en ajouter !
      </p>
    </div>
  </div>
</template>

<style scoped>



</style> 