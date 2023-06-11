<script setup>

import { useQuery } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
import { ref } from 'vue'

const items = ref([1,2,3,4,5,6,7,8,9,10])

// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: async () => {
    const res = await fetch(import.meta.env.VITE_API_URL+'products');
    return await res.json();

  }
})

</script>
<template>
  <div class="sections">
    <div v-if="isLoading">Loading...</div>
    <div v-if="isError">Error: {{ error }}</div>
    <div v-if="data">
      <!-- Loop through example array with animation -->
      <template v-for="section in data" :key="section.id">
          <Transition name="slideIn" appear>
            <Section :style="{ 'transition-delay': section.id * 50 + 'ms' }" :section="section"></Section>
          </Transition>
      </template>
    </div>
  </div>
</template>

<style scoped>


</style> 