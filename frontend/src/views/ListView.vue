<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
  import Autocomplete from '@/components/Autocomplete.vue'
import ProductForm from '@/components/ProductForm.vue'
import RollbackButton from '@/components/RollbackButton.vue'
import StoreSelector from '@/components/StoreSelector.vue'
import Loader from '@/components/Loader.vue'
import axios from 'axios'
import { ref } from 'vue'
import { useProductFormStore } from '../stores/productForm'
import { useActionsStore } from '../stores/actions'
import { useUserStore } from '../stores/user'
import WelcomeCherry from '../components/WelcomeCherry.vue'


const hasProducts = ref(true);
//const stores = ref([])
const queryClient = useQueryClient()

const productFormStore = useProductFormStore();
const actionsStore = useActionsStore();
const userStore = useUserStore()


// Get sections and products from API
const { isLoading, isError, data, error, isStale } = useQuery({
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

    if(!userStore.tutorial) {
      userStore.setTutorial();
    }
  },
});


// Add a product to the list
function addProduct(product) {

  productEdition.mutate(product)
  
}

// Open the product form
function openNewProductForm(product) {

    productFormStore.resetProduct()
    productFormStore.updateProduct({
      name: product,
    })

    productFormStore.updateType('add')

    productFormStore.updateOpen(true)
}


</script>
<template>
  <h1><span class="title">Ma liste</span> <StoreSelector></StoreSelector></h1>
  <div class="sections">
    <div v-if="isLoading"><Loader/></div>
    <div v-if="isError">{{ error.response.data.message }}</div>
    <div v-if="data">
      <!-- Loop through section array with animation -->
      <template v-for="(section, index) in data" :key="section.id">
          <Transition name="slideIn" appear>
            <Section :style="{ 'transition-delay': index * 25 + 'ms' }" :section="section" v-if="section.products.length > 0"></Section>
          </Transition>
      </template>
      <p v-if="!hasProducts && !isLoading " class="alert-info">
        Il n'y a pas encore de produits dans votre liste. <br>Utilisez le bouton ci-dessous pour en ajouter !
      </p>
      <Transition name="fadeIn" appear>
        <WelcomeCherry v-if="!userStore.tutorial && !isLoading" :step="'product'"/>
      </Transition>
    </div>
  </div>
  <template v-if="!isLoading && !isError">
    <div class="controls">
      <RollbackButton v-if="true || actionsStore.visible"></RollbackButton>
      <Autocomplete @selected="addProduct" @new="openNewProductForm" ></Autocomplete>
    </div>
  </template>
  <ProductForm v-if="productFormStore.open" ></ProductForm>
</template>

<style scoped>

.sections {
  margin-bottom: 7rem;
}
.controls {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  padding-bottom: 2rem;
  align-items: center;
  pointer-events: none;

  > * {
    pointer-events: all;
  }
}
h1 {
  display: flex;
  align-items: center;
}
h1 span {
  font-weight: bold;
}
.title {
  flex-shrink: 0;
}
</style> 