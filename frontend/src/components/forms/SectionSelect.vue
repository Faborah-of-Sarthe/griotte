<template>
    <div class="section-select">
        <div class="sections" >
            <div v-if="isLoading" class="loading">
                Chargement des rayons
            </div>
            <template v-if="data" v-for="section in data" :key="section.id">
                <Transition name="slideUp" appear>
                    <div :style="{ 'transition-delay': section.id * 50 + 'ms' }" 
                          class="section" 
                          :class="{selected: section.id == value }" 
                          @click="handleChange(section)">
                        <SectionIcon class="big" :color="section.color"></SectionIcon>
                        <p>{{ section.name }}</p>
                    </div>
                </Transition>
            </template>
        </div>

    </div>
</template>

<script setup>

import { defineProps, ref, computed, onMounted } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'
import SectionIcon from '@/components/SectionIcon.vue'


const props = defineProps(['value'])
const emit = defineEmits(['update:modelValue'])



// Get sections from API
const {  data, isLoading } = useQuery({
    queryKey:  ['sections'],
    queryFn: async () => {

        const res = await axios.get(import.meta.env.VITE_API_URL+'sections', {
            withCredentials: true,
        })

        // Add a default section with id 0
        res.data.unshift({
            id: 0,
            name: 'Aucun rayon',
            color: 0,
            icon: 0
        })

        return res.data
    },
    staleTime: 1000 * 60 * 60,
})

// Emit the selected section
const handleChange = (event) => {
    const sectionId =  parseInt(event.id)
    emit('update:modelValue', sectionId)
}

</script>


<style scoped>

.sections {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    max-height: 25rem;
    overflow-y: scroll;

}

.section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    width: 50%;
    padding: 1rem 0.5rem;
    border-radius: 0.5rem;
}
.section.selected {
    transition: all .1s ease;
    background-color: var(--color-1-light);
}


</style>