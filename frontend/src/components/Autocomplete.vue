<script setup>

import {  ref, watchEffect, watch } from 'vue'
import { useDebouncedRef } from '../utils'
import { useMutation } from '@tanstack/vue-query'
import SectionIcon from '@/components/SectionIcon.vue'
import axios from 'axios'

const open = ref(false)
const inputSearch = ref(null)
const searchTerms = useDebouncedRef('', 400)

const emit = defineEmits(['selected', 'new'])


// Methods
// Toggle the search state
function toggleSearch() {
    open.value = !open.value
  
    // On closing, clear the search terms
    if (!open.value) {
        searchTerms.value = ''
    }
}

// Emit the selected product and close the search
function selectProduct(product) {
    emit('selected', product)
    toggleSearch()
}

// Emit the new product and close the search
function createProduct() {
    emit('new', searchTerms.value)
    toggleSearch()
}


const { isLoading, isError, error, isSuccess, mutate, data } = useMutation({
  mutationFn: (searchTerms) => axios.get(import.meta.env.VITE_API_URL+'products/autocomplete', {
    params: {
      q: searchTerms
    }
  }),
})

// Watch for the open state and focus on the input
watchEffect(() => {
    if (inputSearch.value) {
        inputSearch.value.focus()
    }
})

// Watch for the search terms and emit the event
watch(searchTerms, (value) => {
    if (value.length > 1) {
        mutate(value);
    }
})


</script>

<template>
    <Transition name="slideUp" appear>
        <button v-if="!open" @click="toggleSearch" class="add_button">Ajouter</button>
    </Transition>
    <Transition name="slideUp" appear>
    <div class="results" v-if="open && (isSuccess || isLoading) && searchTerms.length > 1">
        <div class="products" v-if="data?.data.length > 0">
            <div v-for="product in data?.data" :key="product.id" class="result" @click="selectProduct(product)">
                <div class="result__name">{{ product.name }}</div>
                <SectionIcon class="small" :color="product.section[0] ? product.section[0].color : 0"></SectionIcon>
            </div>
        </div>
        <div class="creation">
            <div class="creation_wrapper" @click="createProduct(searchTerms)">
                <p>
                    Créer  <strong>"{{ searchTerms }}"</strong>
                </p>
                <!-- TODO : replace with the plus icon -->
                <SectionIcon class="small" color="1"></SectionIcon>
            </div>
        </div>
    </div>
    </Transition>

    <Transition name="slideUp">
        <input placeholder="Chercher un produit" autocomplete="off" v-model="searchTerms" ref="inputSearch" v-if="open" type="text" class="search" name="search" id="search">
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="toggleSearch" v-if="open">
    </div>
    </Transition>
    
</template>

<style scoped>
.add_button {
  font-size: 0;
  background: var(--color-primary);
  border: none;
  border-radius: 50%;
  width: 5rem;
  height: 5rem;
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);

  cursor: pointer;
  transition: all 0.2s ease-in-out;
  outline: none;
  /* TODO: Cross background */

}


.search {

    position: fixed;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    width: calc(100% - 2rem);
    z-index: 1;
    transition-delay: .2s;
    border: none;
    border-radius: .25rem;
    padding: .8rem 1rem;
    font-size: 1.5rem;
    outline: none;
}

.results {
    position: fixed;
    bottom: 5rem;
    left: 1rem;
    right: 1rem;
    width: calc(100% - 2rem);
    z-index: 1;
    border: none;
    border-radius: .25rem;
    padding: .8rem 1rem;
    font-size: 1.5rem;
    outline: none;
    background: var(--color-background);

    border-radius: .25rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.products::after {
    content: '';
    display: block;
    width: 70%;
    margin-left: auto;
    margin-right: auto;
    height: 1px;
    background: var(--color-9);
    margin-top: 1rem;
}
.creation_wrapper,
.result {
    padding: .5rem 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
}


strong {
    font-weight: 700;
}

.slideUp-enter-active.add_button {
    transition-delay: .3s;
}

.slideUp-enter-from.add_button,
.slideUp-leave-to.add_button {
  opacity: 0;
  transform: translateY(20px) translateX(-50%);
}

</style>