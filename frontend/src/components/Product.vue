<script setup>

// Imports
import { defineProps } from 'vue'

// Props
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})



</script>

<template>
    <div class="product">
        <div class="product-info">
            <h3>{{ product.name }}</h3>
        </div>
    </div>
</template>