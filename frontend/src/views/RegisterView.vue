<script setup>

import { ref, computed } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'
import Cookies from 'universal-cookie'
import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'

const email = ref('')
const password = ref('')
const registerEffective = ref(false)


const register = async () => {

    const data = {
        email: email.value,
        password: password.value
    }
    
    
    // Get Sanctum CSRF cookie
    const csrfCookie = await axios.get(import.meta.env.VITE_AUTH_URL+'sanctum/csrf-cookie')

    // Send login request
    const res = await axios.post(import.meta.env.VITE_AUTH_URL+'register', data)

    
}
// Handle the request with vue-query in order to get the loading state
const { isLoading, isError, error, isSuccess, mutate } = useMutation({
    mutationFn: register,
})



// Call the login function when the form is submitted
function handleRegister() {
    mutate()
}


// Title of the card according to the current register step
const title = computed(() => {
    return registerEffective.value ? 'Bienvenue !' : 'Inscription'
})


</script>

<template>

<div class="register card__container">
    <Transition name="slideUp" appear>
    <Card :title="title" :class="isLoading ? 'card--loading' : ''">
        <div v-if="isSuccess && !isError">
            <p>
                Votre compte a bien été créé !<br>
                Un email  contenant un lien de confirmation a été envoyé à l'adresse : <strong>{{ email }}</strong> <br>Veuillez cliquer sur celui-ci pour activer votre compte.
            </p>
        </div>
        <form v-else @submit.prevent="handleRegister">
            <BaseInput label="E-mail" type="email" placeholder="Ex : ramene-ta@fraise.com" v-model="email"  ></BaseInput>
            <BaseInput label="Mot de passe" class="password" type="password" placeholder="Ex : gri0tt3-0u-big4rr34u?" v-model="password" ></BaseInput>
            <Button type="submit" :disabled="isLoading" design="primary">M'inscrire</Button>
        </form>
      

    </Card>
    </Transition>
</div>

</template>

<style scoped>
strong {
    display: block;
    text-align: center;
    padding: 1rem 0;
    font-weight: 700;
}
</style>