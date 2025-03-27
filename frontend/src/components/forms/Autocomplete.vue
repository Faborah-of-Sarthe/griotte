<template>
  <div class="autocomplete-wrapper">
    <BaseInput
      v-bind="$attrs"
      :modelValue="modelValue"
      @input="handleInput"
      @focus="showSuggestions = true"
      class="autocomplete-input"
      placeholder="Rechercher un ingrédient"
    />
    <Transition name="slideDown">
        <div v-if="showSuggestions && (searchQueryData || isLoadingSearchQuery)" class="suggestions">
            <div class="products" v-if="searchQueryData?.data.length > 0 || isLoadingSearchQuery" :class="{loaded: !isLoadingSearchQuery}">
                <Loader v-if="isLoadingSearchQuery" :inline="true" loading-text="Je cherche..." />
                <div v-for="product in searchQueryData?.data" :key="product.id" class="result" @click="selectSuggestion(product)">
                    <div class="result__name">{{ product.name }}</div>
                    <SectionIcon class="small" :icon="product.sections[0]?.icon ?? 'question'" :color=" product.sections[0]?.color ?? 0"></SectionIcon>
                </div>
            </div>
            <div class="creation" v-if="!isLoadingSearchQuery">
                <div class="creation_wrapper" @click="createProduct(modelValue)">
                    <p>
                        Créer  <strong>"{{ modelValue }}"</strong>
                    </p>
                    <Cross class="small plus" />
                </div>
            </div>
        </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import BaseInput from './BaseInput.vue'
import { useDebouncedRef } from '../../utils'
import { useMutation } from '@tanstack/vue-query'
import axios from 'axios'
import Cross from '../icons/Cross.vue'
import SectionIcon from '../SectionIcon.vue'
import Loader from '../Loader.vue'

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
    default: ''
  },
  apiUrl: {
    type: String,
    required: false,
    default: ''
  },
  minChars: {
    type: Number,
    required: false,
    default: 1
  }
})

const emit = defineEmits(['update:modelValue', 'select'])

const showSuggestions = ref(false)
const debouncedSearch = useDebouncedRef('', 300)
const searchTerm = ref('')


const {
    mutate: searchQuery, 
    isLoading: isLoadingSearchQuery, 
    data: searchQueryData, 
    isSuccess: isSuccessSearchQuery } = useMutation({
  mutationFn: (searchTerms) => axios.get(import.meta.env.VITE_API_URL+'products/autocomplete', {
    params: {
      q: searchTerms
    }
  }),
})


const handleInput = (event) => {
  const value = event.target.value
  emit('update:modelValue', value)
  debouncedSearch.value = value
}

watch(debouncedSearch, (value) => {
  if (value.length >= props.minChars) {
    searchQuery(value)
    showSuggestions.value = true
  }
})

const selectSuggestion = (suggestion) => {
  emit('select', suggestion)
  showSuggestions.value = false
  searchTerm.value = ''
}

const createProduct = (text) => {
  emit('create', {name: text, id: null})
  showSuggestions.value = false
  searchTerm.value = ''
}

// Fermer les suggestions si on clique en dehors
const handleClickOutside = (event) => {
  if (!event.target.closest('.autocomplete-wrapper')) {
    showSuggestions.value = false
    searchTerm.value = ''
  }
}

document.addEventListener('click', handleClickOutside)
</script>

<style scoped>
.autocomplete-wrapper {
  position: relative;
  width: 100%;
}
.plus {
    height: .9rem;
    width: .9rem;
    transform: rotate(45deg);

}
.suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border-top: none;
  border-radius: 0 0 0.25rem 0.25rem;
  overflow-y: auto;
  z-index: 10;
  padding: 1rem;
  box-shadow: 0 0 10px 0 rgba(105, 0, 0, 0.2);
}

.suggestion {
  padding: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.suggestion:hover {
  background-color: var(--color-1-light);
}

:deep(.autocomplete-input) {
    background: var(--color-background);
    position: relative;
    z-index: 11;
    margin-top: 1rem;

}
.products.loaded::after {
    content: '';
    display: block;
    width: 70%;
    margin-left: auto;
    margin-right: auto;
    height: 1px;
    background: var(--color-9);
    margin-top: 1rem;
}

.suggestion.loading {
  text-align: center;
  color: var(--color-9);
  cursor: default;
}

.suggestion.loading:hover {
  background-color: transparent;
}
.creation {
    padding: 1rem;
}
.creation_wrapper,
.result {
    padding: .5rem 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

</style>
