import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useProductFormStore = defineStore('productFormStore', () => {

  const blankProduct = {
    name: '',
    id: null,
    comment: '',
    to_buy: 1,
    section_id: 0,
  }

  // State
  const product = ref({
    ...blankProduct
  })
  const type = ref('add');
  const open = ref(false);


  // Actions
  function updateProduct(newProductData) {
    Object.assign(product.value, newProductData)
  }

  function updateType(newType) {
    type.value = newType;
  }

  function updateOpen(newOpen) {
    open.value = newOpen;
  }

  function resetProduct() {
    product.value = {
      ...blankProduct
    }
  }

  return { product, updateProduct, type, open, updateType, updateOpen, resetProduct}
})
