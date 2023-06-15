<script setup>

import { ref } from 'vue'
import BaseInput from '../components/forms/BaseInput.vue'
import Button from '../components/forms/Button.vue'
import Card from '../components/forms/Card.vue'

const email = ref('')
const password = ref('')



</script>

<template>
    
    <div class="login card__container">
        <Transition name="slideUp" appear>
        <Card title="Connexion">
            <form class="login__form">
                <BaseInput label="E-mail" type="email"  v-model="email"  ></BaseInput>
                <BaseInput label="Mot de passe" type="password" v-model="password" ></BaseInput>
                <Button type="submit" design="primary">Se connecter</Button>
            </form>
        </Card>
        </Transition>
    </div>

</template>

