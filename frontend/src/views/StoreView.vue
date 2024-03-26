<template>
    <div v-if="isError">{{  error }}</div>
    <div v-if="isLoading">Loading...</div>
    <div v-if="data">

        <h1 @click="handleEditStore(data)">{{  data.name }}</h1>
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
            <Button @click="handleNewSection" type="button" >Ajouter un rayon</Button>
        </div>
        <SectionForm v-if="sectionFormStore.open"></SectionForm>
        <StoreForm v-if="storeFormStore.open"></StoreForm>
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
import StoreForm from '../components/StoreForm.vue'
import { ref } from 'vue';
import { useStoreFormStore } from '../stores/storeForm'


const router = useRouter()
const sections = ref([])
const sectionFormStore = useSectionFormStore()
// Get the store form store
const storeFormStore = useStoreFormStore()

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

/**
 * Handle the new section by opening the form and resetting the store
 */
function handleNewSection() {
    sectionFormStore.resetSection()
    // Add the current store id to the section
    sectionFormStore.updateSection({
        store_id: router.currentRoute.value.params.id
    })
    sectionFormStore.updateType('add')
    sectionFormStore.updateOpen(true)
}

/**
 * Handle the store edition by opening the form and resetting the store
 */
 function handleEditStore(selectedStore) {
    storeFormStore.updateStore(selectedStore)
    storeFormStore.updateType('edit')
    storeFormStore.updateOpen(true)
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
    .btn {
        margin-bottom: 1rem;
    }
    h2 {
        color: var(--color-text-alt);
    }
    h1 {
        cursor: pointer;
    }
</style>