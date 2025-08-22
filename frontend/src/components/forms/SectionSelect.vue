<template>
    <div class="section-select">
        <!-- Champ de recherche affiché seulement si plus de 5 rayons -->
        <div v-if="data && data.length > 6" class="filter-container">
            <BaseInput 
                v-model="searchFilter" 
                placeholder="Rechercher un rayon..." 
            />
        </div>
        
        <div class="sections" >
            <div v-if="isLoading" class="loading">
                Chargement des rayons
            </div>
            <template v-if="filteredSections" v-for="(section, index) in filteredSections" :key="section.id">
                <Transition name="slideUp" appear>
                    <div :style="{ '--slide-delay': index  * 25 + 'ms' }" 
                          class="section" 
                          :class="{selected: section.id == value }" 
                          @click="handleChange(section)">
                        <SectionIcon :icon="section.icon" class="big" :color="section.color"></SectionIcon>
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
import BaseInput from '@/components/forms/BaseInput.vue'


const props = defineProps(['value'])
const emit = defineEmits(['update:modelValue'])

// Filter state
const searchFilter = ref('')

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

// Computed property for filtered sections
const filteredSections = computed(() => {
    if (!data.value) return []
    
    if (!searchFilter.value.trim()) {
        return data.value
    }
    
    return data.value.filter(section => 
        section.name.toLowerCase().includes(searchFilter.value.toLowerCase())
    )
})

// Emit the selected section
const handleChange = (event) => {
    const sectionId =  parseInt(event.id)
    emit('update:modelValue', sectionId)
}

</script>


<style scoped>

.filter-container {
    margin-bottom: 1rem;
}

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
p{
    text-align: center;
}

.slideUp-enter-active {
    transition: all 0.2s ease;
    transition-delay: var(--slide-delay);
}

</style>