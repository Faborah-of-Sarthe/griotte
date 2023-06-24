<script setup>

// Imports
import { defineProps, ref, computed } from 'vue'
import Arrow from './icons/Arrow.vue'

// Props
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

// State
const open = ref(false)

// Methods

// Open the product comment if it has one
function openProduct() {
  if (props.product.comment !== '') {
    open.value = !open.value
  }
}

// Computed
const hasComment = computed(() => {
  return props.product.comment !== ''
})

</script>

<template>
    <div class="product">
        <div class="product-info">
            <p class="product-name" :class="{clickable: hasComment}" @click="openProduct">{{ product.name }}  <Arrow class="arrow" :class="{open: open}" v-if="hasComment" ></Arrow></p>
            <Transition name="slideDown">
              <p class="comment" v-if="hasComment && open">
                {{ product.comment }}
              </p>
            </Transition>
        </div>
    </div>
</template>

<style scoped>

  .comment {
    color: var(--color-9);
    padding-left: 1rem;
    transition: all 0.15s ease-in-out;
    white-space: pre;
  }
  .product {
    font-size: 1.2rem;
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

</style>