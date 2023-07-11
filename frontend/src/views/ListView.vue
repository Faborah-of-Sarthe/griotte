<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
import Autocomplete from '@/components/Autocomplete.vue'
import ProductForm from '@/components/ProductForm.vue'
import axios from 'axios'
import { ref } from 'vue'


const hasProducts = ref(false);
const queryClient = useQueryClient()
const productFormOpen = ref(false)
const type = ref('add')
const currentProduct = ref({})

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

// Mutation to add a product to the list
const productEdition = useMutation({
  mutationFn: (productData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'products/' + productData.id, {
      to_buy: 1
    })
  },
  onSuccess: () => {
    queryClient.invalidateQueries('products')
  },
  onError: (error) => {
    console.log(error)
  },
});


// Add a product to the list
function addProduct(product) {

  productEdition.mutate(product)
  
}

// Open the product form
function openNewProductForm(product) {
  currentProduct.value = {
    name: product,
    id: null,
    comment: '',
    to_buy: 1
  }
  productFormOpen.value = true
}


</script>
<template>
  <h1>Ma liste</h1>
  <div class="sections">
    <div v-if="isLoading">Loading...</div>
    <div v-if="isError">Error: {{ error }}</div>
    <div v-if="data">
      <!-- Loop through section array with animation -->
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
  <template v-if="!isLoading && !isError && hasProducts">
    <Autocomplete @selected="addProduct" @new="openNewProductForm" ></Autocomplete>
  </template>
  <ProductForm v-if="productFormOpen" :type="type" :product="currentProduct" @close="productFormOpen = false"></ProductForm>
</template>

<style scoped>



.sections {
  margin-bottom: 7rem;
}

</style> 