<script setup>

// Imports
import { defineProps, ref, computed } from 'vue'
import Arrow from './icons/Arrow.vue'
import CheckMark from './icons/CheckMark.vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import { useProductFormStore } from '../stores/productForm'

const queryClient = useQueryClient()

// Props
const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  section_id: {
    type: Number,
    required: true
  },
  color: {
    type: [String, Number],
    required: true
  }
})

const productFormStore = useProductFormStore()

// State
const open = ref(false)
const visible = ref(true)

// Methods
const { isLoading, isError, error, isSuccess, mutate } = useMutation({
  mutationFn: (productData) => {
    return axios.patch(import.meta.env.VITE_API_URL + 'products/' + props.product.id, productData)
  },
  onSuccess: () => {
    visible.value = false
    queryClient.invalidateQueries('products')
  },
  onError: (error) => {
    console.log(error)
  },
});

// Open the product comment if it has one
function openProduct() {
  if (props.product.comment !== '') {
    open.value = !open.value
  }
}

function checkProduct() {
  mutate({
    to_buy: 0
  })
}

function handleLongPress() {
  productFormStore.updateProduct(props.product)
  productFormStore.updateProduct({section_id: props.section_id})
  productFormStore.updateType('edit')
  productFormStore.updateOpen(true)
}

// Computed
const hasComment = computed(() => {
  return !!props.product.comment 
})

</script>

<template>
  <Transition name="slideIn" appear>
    <div v-if="visible" class="product">
      <div class="checkbox-container">
        <label :for="'product' + product.id">
          <input :onChange="checkProduct" type="checkbox" class="check" :id="'product' + product.id" name="check">
          <CheckMark :class="'svg-stroke-'+ color"></CheckMark>
          <span>Cocher</span></label>
      </div>
      <div class="product-info">
        <p  class="product-name" :class="{clickable: hasComment}"  v-on="!productFormStore.open ? {click: openProduct } : {}" v-longpress:500="handleLongPress" >{{ product.name }}  <Arrow class="arrow" :class="{open: open}" v-if="hasComment" ></Arrow></p>
        <Transition name="slideDown">
          <p class="comment" v-if="hasComment && open">
            {{ product.comment }}
          </p>
        </Transition>
      </div>
    </div>
  </Transition>
</template>

<style scoped lang="scss">

  .comment {
    color: var(--color-9);
    transition: all 0.15s ease-in-out;
    white-space: pre-wrap;
  }
  .product {
    font-size: 1.2rem;
    display: flex;
    align-items: flex-start;
  }
  .arrow {
    height: .7rem;
    position: relative;
    padding-left: 0.25rem;
    top: 1px;
    transition: all 0.1s ease-in-out;
  }
  .arrow.open {
    transform: rotate(90deg);
    bottom: 5px;
    top: 0;
  }
  .clickable {
    cursor: pointer;
  }

  .check,
  label span {
    display: none;
  }
  .checkbox-container {
    position: relative;
    top: 2px;
  }
  .check:checked + .checkmark {
    stroke-dashoffset: 0;
    background: #474747;
  }
  .checkmark {
    width: 1.5rem;
    height: 1.5rem;
    border: 2px solid #474747;
    border-radius: .25rem;
    display: block;
    stroke-width: 4px;
    // stroke-miterlimit: 10;
    stroke-dashoffset: 50;
    stroke-dasharray: 50;
    margin-right: 0.5rem;
    transition: background-color 0.2s ease-in-out, stroke-dashoffset 0.3s ease-in-out;
    cursor: pointer;

    path {
      transform: scale(1.4)
    }
  }


</style>