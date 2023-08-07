<script setup>

// Imports
import { defineProps } from 'vue'
import Product from '@/components/Product.vue'
import SectionIcon from '@/components/SectionIcon.vue'


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
      <SectionIcon class="big" :color="section.color"></SectionIcon>
        <h2>{{ section.name }}</h2>
    </header>
    <div class="products">
      <div v-for="product in section.products" :key="product.id">
        <Product :product="product" :section_id="section.id" :color="section.color"></Product>
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
    margin-right: 1rem;
  }
  
</style>
