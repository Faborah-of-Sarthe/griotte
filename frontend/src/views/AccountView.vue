<template>
    <h1 class="title">Mon compte</h1>
    <div v-if="isLoading"><Loader/></div>
    <form v-else class="user__form" @submit.prevent="editMutation.mutate">
        <div class="alert-info">
            Vous devez renseigner votre mot de passe actuel pour modifier votre mot de passe ou votre e-mail.
        </div>
        <BaseInput label="E-mail" type="email"  v-model="user.email"  ></BaseInput>
        <BaseInput class="password" label="Mot de passe actuel" type="password" v-model="password" ></BaseInput>
        <BaseInput class="password" label="Nouveau mot de passe" type="password" v-model="newPassword" ></BaseInput>
        <Button type="submit" :disabled="loading" :loading="loading" design="primary">Modifier</Button>
    </form>
</template>  
<script setup>

import { useMutation, useQuery } from '@tanstack/vue-query'
import axios from 'axios'
import BaseInput from '../components/forms/BaseInput.vue';
import Button from '../components/forms/Button.vue';
import Loader from '../components/Loader.vue';
import { computed, ref } from 'vue'

const password = ref('')
const newPassword = ref('')
const user = ref({});
const { isLoading, data } = useQuery({
    queryKey:  ['user'],
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL+'user', {
            withCredentials: true,
        })

        user.value = res.data
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
</script>