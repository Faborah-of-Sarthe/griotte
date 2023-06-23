<script setup>

import { ref, computed } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'
import Cookies from 'universal-cookie'
import axios from 'axios'

const email = ref('')
const password = ref('')
const registerEffective = ref(false)

const cookies = new Cookies()

const register = async () => {

    const data = {
        email: email.value,
        password: password.value
    }
    
    
    // Get Sanctum CSRF cookie
    const csrfCookie = await axios.get(import.meta.env.VITE_AUTH_URL+'sanctum/csrf-cookie')

    // Send login request
    try {

        const res = await axios.post(import.meta.env.VITE_AUTH_URL+'register', data)
        registerEffective.value = true
        // TODO: Send email confirmation
    }
    catch (error) {
    //     // TODO: handle errors
        console.log(error)
    }
    
}


// Title of the card according to the current register step
const title = computed(() => {
    return registerEffective.value ? 'Bienvenue !' : 'Inscription'
})


</script>

<template>

<div class="register card__container">
    <Transition name="slideUp" appear>
    <Card :title="title">
        <form v-if="!registerEffective" @submit.prevent="register">
            <BaseInput label="E-mail" type="email" placeholder="Ex : ramene-ta@fraise.com" v-model="email"  ></BaseInput>
            <BaseInput label="Mot de passe" class="password" type="password" placeholder="Ex : gri0tt3-0u-big4rr34u?" v-model="password" ></BaseInput>
            <Button type="submit" design="primary">M'inscrire</Button>
        </form>
        <div v-else>
            <p>
                Votre compte a bien été créé !<br>
                Un email  contenant un lien de confirmation a été envoyé à l'adresse : <strong>{{ email }}</strong> <br>Veuillez cliquer celui-ci pour activer votre compte.
            </p>
        </div>

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