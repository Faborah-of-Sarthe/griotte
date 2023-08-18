<script setup>

import { ref } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'
import { useRouter } from 'vue-router'
import Cookies from 'universal-cookie'
import { useUserStore } from '../stores/user'
import { saveUser } from '../utils'
import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'


const email = ref('')
const password = ref('')
const router = useRouter()

// Function making the login request
const login = async () => {

    const data = {
        email: email.value,
        password: password.value
    }


    // Get Sanctum CSRF cookie
    const csrfCookie = await axios.get(import.meta.env.VITE_AUTH_URL+'sanctum/csrf-cookie')

    // Send login request
    const res = await axios.post(import.meta.env.VITE_AUTH_URL+'login', data)

    // If the API responds with a 200 status code
    if (res.status === 200) {

        // Get the user current store
        const userStore = await axios.get(import.meta.env.VITE_API_URL+'stores/current', {
            withCredentials: true,
        });
        const user = {
            ...res.data,
            currentStore: userStore.data
        }

        // Set the user in the store
        saveUser(user)
        router.push('my-list')
    } else {
        // TODO: handle errors
        console.log('error')
    }
}

// Handle the request with vue-query in order to get the loading state
const { isLoading, isError, error, isSuccess, mutate } = useMutation({
    mutationFn: login,
})



// Call the login function when the form is submitted
function handleLogin() {
    mutate()
}


</script>

<template>
    
    <div class="login card__container">
        <Transition name="slideUp" appear>
        <Card title="Connexion" :class="isLoading ? 'card--loading' : ''">
            <form class="login__form" @submit.prevent="handleLogin">
                <BaseInput label="E-mail" type="email"  v-model="email"  ></BaseInput>
                <BaseInput class="password" label="Mot de passe" type="password" v-model="password" ></BaseInput>
                <Button type="submit" :disabled="isLoading" design="primary">Se connecter</Button>
            </form>
            <div class="links">
                <RouterLink class="login__link" to="/">Mot de passe oublié ?</RouterLink>
            </div>
        </Card>
        </Transition>
    </div>

</template>

<style scoped>

.login__form {
    margin-bottom: 1rem;
}
.links {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 1rem;
}

</style>