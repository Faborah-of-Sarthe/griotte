<template>
    <div v-if="isError">{{  error }}</div>
    <div v-if="isLoading">Loading...</div>
    <div v-if="data">

        <h1>{{  data.name }}</h1>
        <h2>Rayons</h2>
        <div class="sections">
            <section v-for="section in data.sections" :key="section.id" :class="'bg-light-color-' +  section.color ">
                <SectionIcon class="big" :color="section.color"></SectionIcon>
                <p>{{ section.name }}</p>
            </section>
        </div>
        <div>
            <Button type="button" >Ajouter un rayon</Button>
        </div>
    </div>

</template>
<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';
import { useRouter } from 'vue-router'
import Button from '../components/forms/Button.vue'
import SectionIcon from '../components/SectionIcon.vue'

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

<style scoped>

    
    .icon {
        margin-right: 1rem;
    }

    section {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
        display: flex;
        align-items: center;
        font-size: 1.2rem;
    }

</style>