<script setup>

// Imports
import { defineProps } from 'vue'
import Product from '@/components/Product.vue'


// Props
const props = defineProps({
  section: {
    type: Object,
    required: true
  }
})

</script>

<template>
  <section :class="'bg-light-color-' +  section.color ">
    <header>
        <div class="icon" :class="'bg-color-' +  section.color "></div>
        <h2>{{ section.name }}</h2>
    </header>
    <div class="products">
      <div v-for="product in section.products" :key="product.id">
        <Product :product="product"></Product>
      </div>
    </div>
  </section>
</template>

<style scoped>

  section {
    padding: 1rem;
    margin-bottom: 1rem;
  }

  section header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
  }
  .icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    margin-right: 1rem;
  }
</style>
