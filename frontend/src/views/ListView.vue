<script setup>

import { useQuery } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
import { ref } from 'vue'
import Cookies from 'universal-cookie'


const cookies = new Cookies()

// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: async () => {

    const res = await fetch(import.meta.env.VITE_API_URL+'products', {
      method: 'GET',
      credentials: 'include',
      headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': cookies.get('XSRF-TOKEN')
      },
    });
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
            <Section :style="{ 'transition-delay': section.id * 50 + 'ms' }" :section="section" v-if="section.products.length > 0"></Section>
          </Transition>
      </template>
    </div>
  </div>
</template>

<style scoped>


</style> 