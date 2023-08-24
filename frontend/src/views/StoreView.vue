<template>
    <div v-if="isError">{{  error }}</div>
    <div v-if="isLoading">Loading...</div>
    <div v-if="data">

        <h1>{{  data.name }}</h1>
        <h2>Rayons</h2>
        <div class="sections">
            <div v-for="section in data.sections" :key="section.id" class="section">
                <h3>{{ section.name }}</h3>
            </div>
        </div>
    </div>

</template>
<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';
import { useRouter } from 'vue-router'

const router = useRouter()

// Load the store data from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['store' + router.currentRoute.value.params.id],
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'stores/' +  router.currentRoute.value.params.id, {
      withCredentials: true,

    })
    return res.data

  },
})

</script>