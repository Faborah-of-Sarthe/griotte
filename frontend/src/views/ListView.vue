<script setup>

import { useQuery } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'

// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: () => {
    return fetch(import.meta.env.VITE_API_URL + 'products').then((res) =>
      res.json()
    )
  }
})

</script>
<template>
  <div class="sections">
    <div v-if="isLoading">Loading...</div>
    <div v-if="isError">Error: {{ error }}</div>
    <div v-if="data">
      <div v-for="section in data" :key="section.id">
          <Section :section="section"></Section>
      </div>
    </div>
  </div>
</template>

<style>

</style>