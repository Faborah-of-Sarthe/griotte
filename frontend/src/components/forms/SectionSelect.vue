<template>
    <div class="section-select">
        <label for="section">Rayon</label>
        <select name="section" id="section" :value="currentSection" @change="handleChange">
            <option v-for="section in data" :key="section.id" :value="section.id">{{ section.name }}</option>
        </select>
        <!-- TODO : Replace with custom select -->

    </div>
</template>

<script setup>

import { defineProps, ref, computed, onMounted } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'

const props = defineProps({

    currentSection: {
        type: Number,
        required: false,
        default: 0
    }
})

const emit = defineEmits(['update:currentSection'])


// Get sections from API
const {  data } = useQuery({
    queryKey:  ['sections'],
    queryFn: async () => {

        const res = await axios.get(import.meta.env.VITE_API_URL+'sections', {
            withCredentials: true,
        })

        // Add a default section with id 0
        res.data.unshift({
            id: 0,
            name: 'Aucun rayon'
        })

        return res.data
    },
    staleTime: 1000 * 60 * 60,
})

// Emit the selected section
const handleChange = (event) => {
    const sectionId =  parseInt(event.target.value)
    emit('update:currentSection', sectionId)
}

</script>


<style scoped>

label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
}

</style>