<script setup>

import { useQuery } from '@tanstack/vue-query'
import Section from '@/components/Section.vue'
import Cookies from 'universal-cookie'
import { defaultOptions } from '@/utils'
import axios from 'axios'


const cookies = new Cookies()

// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'products', {
      withCredentials: true,

    })
    return res.data



  },
})

</script>
<template>
  <h1>Ma liste</h1>
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