<template>
    <div class="forgot-cart card__container">
        <Transition name="slideUp" appear>
        <Card title="Mot de passe oublié" :class="isLoading ? 'card--loading' : ''">
            <form class="login__form" @submit.prevent="handleRequestPassword">
                <p class="alert-info">
                    Entrez votre adresse e-mail pour recevoir un lien de réinitialisation de mot de passe.
                </p>
                <BaseInput label="E-mail" type="email"  v-model="email"  ></BaseInput>
                <Button type="submit" :disabled="isLoading" :loading="isLoading" design="primary">Envoyer</Button>
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

const email = ref('')

const { isLoading, isError, error, isSuccess, mutate } = useMutation({
    mutationFn: requestPassword,
})

function handleRequestPassword() {
    mutate(email.value)
}

async function requestPassword(email) {
    const res = await axios.post(import.meta.env.VITE_AUTH_URL+'forgot-password', {
        email
    });
}


</script>