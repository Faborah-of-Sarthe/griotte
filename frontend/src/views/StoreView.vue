<template>
    <div v-if="isError">{{  error }}</div>
    <div v-if="isLoading"><Loader /></div>
    <div v-if="data">

        <h1 @click="handleEditStore(data)">{{  data.name }}</h1>
        <h2>Rayons</h2>
        <div class="sections">
            
            <draggable handle=".drag" v-if="(!userStore.tutorial && sections.length > 1) || userStore.tutorial" v-model="sections" tag="div" item-key="id" @change="reOrderSections">
                <template #item="{ element: section }">
                    <section @click="handleSectionEdition(section)" :class="'bg-light-color-' +  section.color ">
                        <SectionIcon class="big" :icon="section.icon" :color="section.color"></SectionIcon>
                        <p >{{ section.name }}</p>
                        <DragIcon class="drag" />
                        <div class="cross" @click.stop="openModalSection = true; deleteSectionId = section.id"><Cross></Cross></div>
                    </section>
                </template>

            </draggable>
            <p v-if="!isStale && !isLoading && sections.length == 0 && userStore.tutorial">Aucun rayon pour le moment</p>
            <WelcomeCherry v-else-if="!userStore.tutorial && sections.length <= 1" :step="'section'"/>
            <WelcomeCherry v-else-if="!userStore.tutorial && sections.length > 1" :step="'section2'"/>

        </div>
        <div class="buttons wide">
            <Button  @click="openModal = true" v-if="data.id != userStore.user.currentStore && userStore.tutorial" class="btn btn--secondary" type="button" >Supprimer le magasin</Button>
            <Button @click="handleNewSection" type="button" >Ajouter un rayon</Button>
        </div>
        <SectionForm  v-if="sectionFormStore.open"></SectionForm>
        <StoreForm v-if="storeFormStore.open"></StoreForm>
        <Modal v-if="openModal" @close="openModal = false" title="Suppression" :buttons="true" @validate="handleDeleteStore">
            <template #content>
                <p>Voulez-vous vraiment supprimer ce magasin ?</p>
                <p>Attention, cette action est définitive !</p>
            </template>
        </Modal>
        <Modal v-if="openModalSection" @close="openModalSection = false; deleteSectionId = null" title="Suppression" :buttons="true" @validate="handleDeleteSection">
            <template #content>
                <p>Voulez-vous vraiment supprimer ce rayon ?</p>
                <p>Attention, cette action est définitive !</p>
            </template>
        </Modal>
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
import Modal from '../components/Modal.vue';
import { useToast } from 'vue-toastification'
import { useUserStore } from '../stores/user';
import Loader from '../components/Loader.vue';
import WelcomeCherry from '../components/WelcomeCherry.vue';
import Cross from '@/components/icons/Cross.vue'
import DragIcon from '@/components/icons/Drag.vue'



const router = useRouter()
const sections = ref([])
const sectionFormStore = useSectionFormStore()
const userStore = useUserStore()
// Get the store form store
const storeFormStore = useStoreFormStore()

const openModal = ref(false)
const openModalSection = ref(false)
const deleteSectionId = ref(null)
const toast = useToast()

// Load the store data from API
const { isLoading, isError, data, error, isStale } = useQuery({
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
function handleSectionEdition(selectedSection, e) {
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

/**
 * Handle the store deletion by sending a delete request to the API
 */
function handleDeleteStore() {
    axios.delete(import.meta.env.VITE_API_URL+'stores/' +  router.currentRoute.value.params.id, {
        withCredentials: true,
    }).then(() => {
        router.push({ name: 'my-stores' })
    })
}

/**
 * Handle the section deletion by sending a delete request to the API
 */
function handleDeleteSection() {
    axios.delete(import.meta.env.VITE_API_URL+'sections/' + deleteSectionId.value, {
        withCredentials: true,
    }).then(() => {
        queryClient.invalidateQueries('sections')
        openModalSection.value = false
    })
}

</script>

<style scoped>

    
    .icon {
        margin-right: 1rem;
        flex-shrink: 0;

    }

    section {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
        display: flex;
        align-items: center;
        font-size: 1.2rem;
    }
    .btn {
        margin-bottom: 1rem;
    }
    h2 {
        color: var(--color-text-alt);
        margin-bottom: 2rem;
    }
    h1 {
        cursor: pointer;
    }
    .sortable-ghost {
        background: var(--color-9-light);
        overflow: hidden;
        transition: all 0.3s; 
        .icon {
            background: var(--color-9);
        }
    }
    .sortable-ghost + section {
        transform: translate(5px, 0);
        transition: transform 0.1s;
    }
    .cross {
        width: 1rem;
        display: flex;
        justify-content: center;
        margin-left: .5rem;
        flex-shrink: 0;
    }
    .drag {
        flex-shrink: 0;
        width: 2rem;
        margin-left: auto;
        color: var(--color-text-alt);
        cursor:grab;

    }
</style>