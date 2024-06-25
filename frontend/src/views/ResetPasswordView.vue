<template>
    <div class="forgot-cart card__container">
        <Transition name="slideUp" appear>
        <Card title="Réinitialiser mon mot de passe" :class="isLoading ? 'card--loading' : ''">
            <form class="login__form" @submit.prevent="handleResetPassword">
                <p class="alert-info">
                    Entrez votre nouveau mot de passe.
                </p>
                <BaseInput class="password" label="Nouveau mot de passe" type="password" v-model="password" ></BaseInput>

                <Button type="submit" :disabled="isLoading" :loading="isLoading" design="primary">Réinitialiser</Button>
            </form>
        </Card>
        </Transition>
    </div>

</template>

<script setup>
import Card from '../components/forms/Card.vue';
import BaseInput from '../components/forms/BaseInput.vue';
import Button from '../components/forms/Button.vue';
import { useMutation } from '@tanstack/vue-query';
import { ref } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const password = ref('')
const route = useRoute();
const router = useRouter();

const token = route.params.token;
const email = route.query.email;

if(!token || !email) {
    router.push({ path: '/forgot-password' })
}


const { isLoading, isError, error, isSuccess, mutate } = useMutation({
    mutationFn: resetPassword,
    onSuccess: (data) => {
        router.push({ path: '/login' })
    },
})

function handleResetPassword() {
    mutate(
        {
            email,
            password: password.value,
            token
        }
    )
}

async function resetPassword(params) {
    const res = await axios.post(import.meta.env.VITE_AUTH_URL+'reset-password', params);
}


</script>