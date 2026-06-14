<script setup>
import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import Button from '@/components/forms/Button.vue'
import Loader from '@/components/Loader.vue'
import Modal from '@/components/Modal.vue'
import TagForm from '@/components/TagForm.vue'
import { useTagFormStore } from '@/stores/tagForm'

const queryClient = useQueryClient()
const tagFormStore = useTagFormStore()
const tagToDelete = ref(null)

const { data: tags, isLoading, error } = useQuery({
    queryKey: ['tags'],
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL + 'tags')
        return res.data
    }
})

const { mutate: deleteTagMutation } = useMutation({
    mutationFn: (tagId) => {
        return axios.delete(import.meta.env.VITE_API_URL + 'tags/' + tagId)
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['tags'])
        queryClient.invalidateQueries(['recipes'])
        tagToDelete.value = null
    }
})

function handleNewTag() {
    tagFormStore.resetTag()
    tagFormStore.updateType('add')
    tagFormStore.updateOpen(true)
}

function handleEditTag(tag) {
    tagFormStore.resetTag()
    tagFormStore.updateTag(tag)
    tagFormStore.updateType('edit')
    tagFormStore.updateOpen(true)
}

function handleDeleteTag() {
    if (tagToDelete.value) {
        deleteTagMutation(tagToDelete.value.id)
    }
}
</script>

<template>
    <div class="header">
        <h1>Tags de recettes</h1>
        <RouterLink :to="{ name: 'my-recipes' }">Retour aux recettes</RouterLink>
    </div>

    <Loader v-if="isLoading" />
    <p v-else-if="error" class="alert-info">Une erreur est survenue lors du chargement des tags.</p>
    <div v-else>
        <p v-if="tags.length === 0" class="alert-info">Vous n'avez pas encore de tags.</p>
        <ol v-else>
            <li v-for="tag in tags" :key="tag.id" class="tag-item">
                <span class="tag-name">{{ tag.name }}</span>
                <div class="tag-actions">
                    <Button type="button" design="secondary" class="small" @click="handleEditTag(tag)">
                        Modifier
                    </Button>
                    <Button type="button" design="secondary" class="small" @click="tagToDelete = tag">
                        Supprimer
                    </Button>
                </div>
            </li>
        </ol>

        <Button type="button" @click="handleNewTag">Ajouter un tag</Button>
    </div>

    <TagForm v-if="tagFormStore.open" />

    <Modal
        v-if="tagToDelete"
        @close="tagToDelete = null"
        title="Suppression"
        :buttons="true"
        @validate="handleDeleteTag"
    >
        <template #content>
            <p>Voulez-vous vraiment supprimer le tag "{{ tagToDelete.name }}" ?</p>
            <p>Il sera aussi retiré des recettes associées.</p>
        </template>
    </Modal>
</template>

<style lang="scss" scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

ol {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-left: 0;
    margin-bottom: 1rem;
}

.tag-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    background: var(--color-secondary);
    padding: 1rem;
    border-radius: 5px;
}

.tag-name {
    color: var(--color-text);
    font-size: 1.5rem;
    font-weight: 700;
    word-break: break-word;
}

.tag-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: flex-end;

    :deep(.btn) {
        margin: 0;
        font-size: 1rem;
    }
}
</style>
