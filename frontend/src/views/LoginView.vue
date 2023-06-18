<script setup>

import { ref } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'
import { saveUser } from '../utils'
import { useRouter } from 'vue-router'


const email = ref('')
const password = ref('')
const router = useRouter()

const login = () => {
    console.log('login')

    const data = {
        email: email.value,
        password: password.value
    }

    // TODO: Send data to API
    // When the API responds, save the user in localStoragec
    saveUser(data)
    router.push('my-list')
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