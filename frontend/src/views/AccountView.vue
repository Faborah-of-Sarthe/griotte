<template>
    <h1 class="title">Paramètres</h1>
    <div v-if="isLoading"><Loader/></div>
    <template v-else>
        <div class="user__form settings__form">
            <div class="alert-info">
                Personnalisez votre expérience sur Griotte ci-dessous.
            </div>
            <Checkbox
                label="Positionner les produits non classés avant les rayons"
                :model-value="user.settings.unclassified_first"
                :disabled="settingsLoading"
                @update:modelValue="updateSetting('unclassified_first', $event)"
            />
            <Checkbox
                label="Garder l'écran allumé sur ma liste de courses"
                :model-value="user.settings.keep_screen_awake"
                :disabled="settingsLoading"
                @update:modelValue="updateSetting('keep_screen_awake', $event)"
            />
        </div>
        <hr class="settings__separator">
        <form class="user__form" @submit.prevent="editMutation.mutate">
            <h2>Mes informations</h2>
            <div class="alert-info">
                Vous devez renseigner votre mot de passe actuel pour modifier votre mot de passe ou votre e-mail.
            </div>
            <BaseInput label="E-mail" type="email"  v-model="user.email"  ></BaseInput>
            <BaseInput class="password" label="Mot de passe actuel" type="password" v-model="password" ></BaseInput>
            <BaseInput class="password" label="Nouveau mot de passe" type="password" v-model="newPassword" ></BaseInput>
            <Button type="submit" :disabled="loading" :loading="loading" design="primary">Modifier</Button>
        </form>
    </template>
</template>  
<script setup>

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import BaseInput from '../components/forms/BaseInput.vue';
import Button from '../components/forms/Button.vue';
import Checkbox from '../components/forms/Checkbox.vue';
import Loader from '../components/Loader.vue';
import { computed, ref } from 'vue'
import { useUserStore } from '../stores/user';

const password = ref('')
const newPassword = ref('')
const user = ref({
    settings: {},
});
const queryClient = useQueryClient()
const userStore = useUserStore()

const { isLoading } = useQuery({
    queryKey:  ['user'],
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL+'user', {
            withCredentials: true,
        })

        user.value = res.data
        userStore.setSettings(res.data.settings)
        return res.data
    },
})

const loading = computed(() => (isLoading.value || editMutation.isLoading.value))


// // Mutation to edit the user
const editMutation = useMutation({
    mutationFn: () => {
        return axios.put(import.meta.env.VITE_API_URL + 'user', {
            email: user.value.email,
            password: password.value,
            new_password: newPassword.value,
        })
    },
   
});

const settingsMutation = useMutation({
    mutationFn: (settings) => {
        return axios.put(import.meta.env.VITE_API_URL + 'user/settings', settings, {
            withCredentials: true,
        })
    },
    onSuccess: (res, updatedSettings) => {
        const settings = res.data.user.settings
        user.value.settings = settings
        userStore.setSettings(settings)

        if (Object.prototype.hasOwnProperty.call(updatedSettings, 'unclassified_first')) {
            queryClient.invalidateQueries('products')
        }
    },
});

const settingsLoading = computed(() => settingsMutation.isLoading.value)

function updateSetting(settingKey, value) {
    user.value.settings[settingKey] = value
    settingsMutation.mutate({
        [settingKey]: value,
    })
}
</script>

<style scoped>
.settings__separator {
    border: 0;
    border-top: 1px solid #e5e5e5;
    margin: 2rem 0;
}
</style>