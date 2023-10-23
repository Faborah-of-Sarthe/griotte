<template>
    <div v-if="isError">{{  error }}</div>
    <div v-if="isLoading">Loading...</div>
    <div v-if="data">

        <h1>{{  data.name }}</h1>
        <h2>Rayons</h2>
        <div class="sections">
            
            <draggable v-model="sections" tag="div" item-key="id" @change="reOrderSections">
                <template #item="{ element: section }">
                    <section @click="handleSectionEdition(section)" :class="'bg-light-color-' +  section.color ">
                        <SectionIcon class="big" :icon="section.icon" :color="section.color"></SectionIcon>
                        <p >{{ section.name }}</p>
                        <!-- TODO: drag icon -->
                    </section>
                </template>

            </draggable>
            <div v-if="data.sections.length == 0">
                <p>Aucun rayon pour le moment</p>
            </div>

        </div>
        <div>
            <Button type="button" >Ajouter un rayon</Button>
        </div>
        <SectionForm v-if="sectionFormStore.open"></SectionForm>
    </div>

</template>
<script setup>

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios';  
import   draggable  from 'vuedraggable'
import { useRouter } from 'vue-router'
import Button from '../components/forms/Button.vue'
import SectionIcon from '../components/SectionIcon.vue'
import { useSectionFormStore } from '@/stores/sectionForm'
import SectionForm from '@/components/SectionForm.vue'
import { ref } from 'vue';

const router = useRouter()
const sections = ref([])
const sectionFormStore = useSectionFormStore()

// Load the store data from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['store' + router.currentRoute.value.params.id],
  refetchOnWindowFocus: false, 
  queryFn: async () => {

    const res = await axios.get(import.meta.env.VITE_API_URL+'stores/' +  router.currentRoute.value.params.id, {
      withCredentials: true,

    })
    sections.value = res.data.sections
    return res.data

  },
})

const queryClient = useQueryClient()

// Send the new order to the API
const reOrderSections = async (event) => {
    const res = await axios.put(import.meta.env.VITE_API_URL+'stores/' +  router.currentRoute.value.params.id + '/sections/reorder', {
        sections: sections.value.map((section, index) => {
            return {
                id: section.id,
                order: index + 1
            }
        })
    }, {
        withCredentials: true,
    })
    queryClient.invalidateQueries('store' + router.currentRoute.value.params.id)
}

/**
 * Handle the section edition by opening the form and setting the section to edit in the store
 * @param { Object } selectedSection The section to edit
 */
function handleSectionEdition(selectedSection) {
    sectionFormStore.updateSection(selectedSection)
    sectionFormStore.updateType('edit')
    sectionFormStore.updateOpen(true)
}


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
        cursor:grab;
    }

</style>