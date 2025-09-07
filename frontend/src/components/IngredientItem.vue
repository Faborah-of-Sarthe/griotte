<script setup>
import { defineProps, defineEmits } from 'vue'
import BaseInput from './forms/BaseInput.vue'
import Checkbox from './forms/Checkbox.vue'
import Autocomplete from './forms/Autocomplete.vue'

const props = defineProps({
  ingredient: {
    type: Object,
    required: true
  },
  isExisting: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:ingredient', 'product-select', 'product-create'])

const updateQuantity = (value) => {
  emit('update:ingredient', { ...props.ingredient, quantity: value })
}

const updateInclude = (value) => {
  emit('update:ingredient', { ...props.ingredient, include: value })
}

const updateName = (value) => {
  emit('update:ingredient', { ...props.ingredient, name: value })
}

const handleProductSelect = (selectedProduct) => {
  emit('product-select', selectedProduct, props.ingredient)
}

const handleProductCreate = (newProduct) => {
  emit('product-create', newProduct, props.ingredient)
}
</script>

<template>
  <div 
    class="ingredient-item"
    :class="{ 'included': ingredient.include }"
  >
    <div class="ingredient-row">
      <!-- Nom de l'ingrédient -->
      <div class="ingredient-name-section">
        <!-- Produit existant : affichage en lecture seule -->
        <div v-if="isExisting" class="ingredient-name-display">
          {{ ingredient.existingProduct.name }}
        </div>
        <!-- Nouveau produit : autocomplete modifiable -->
        <Autocomplete
          v-else
          :modelValue="ingredient.name"
          @update:modelValue="updateName"
          placeholder="Nom de l'ingrédient"
          class="ingredient-name-input"
          @select="handleProductSelect"
          @create="handleProductCreate"
        />
      </div>
      
      <div class="ingredient-actions">
        <!-- Quantité - toujours modifiable -->
        <div class="ingredient-quantity-section">
          <BaseInput
            :modelValue="ingredient.quantity"
            @update:modelValue="updateQuantity"
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
            @update:modelValue="updateInclude"
            type="option"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ingredient-item {
  &.included {
    // Styles pour les ingrédients inclus si nécessaire
  }
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

@media (max-width: 768px) {
  .ingredient-include-section {
    padding-top: 0;
    justify-content: flex-start;
  }
}
</style>
