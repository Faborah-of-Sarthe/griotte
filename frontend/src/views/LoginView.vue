<script setup>

import { ref } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'
import { saveUser } from '../utils'
import { useRouter } from 'vue-router'
import Cookies from 'universal-cookie'


const email = ref('')
const password = ref('')
const router = useRouter()

const cookies = new Cookies()

const login = async () => {

    const data = {
        email: email.value,
        password: password.value
    }
    

    // Get Sanctum CSRF cookie
    const csrfCookie = await fetch(import.meta.env.VITE_AUTH_URL+'sanctum/csrf-cookie', {
        method: 'GET',
        credentials: 'include'
    })



    // Send login request
    const res = await fetch(import.meta.env.VITE_AUTH_URL+'login', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            // Add the CSRF token in the request headers
            'X-XSRF-TOKEN': cookies.get('XSRF-TOKEN')
        },
        body: JSON.stringify(data)
    })


    // If the API responds with a 200 status code
    if (res.status === 200) {
        router.push('my-list')
    } else {
        // TODO: handle errors
        console.log('error')
    }
}



</script>

<template>
    
    <div class="login card__container">
        <Transition name="slideUp" appear>
        <Card title="Connexion">
            <form class="login__form" @submit.prevent="login">
                <BaseInput label="E-mail" type="email"  v-model="email"  ></BaseInput>
                <BaseInput class="password" label="Mot de passe" type="password" v-model="password" ></BaseInput>
                <Button type="submit" design="primary">Se connecter</Button>
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