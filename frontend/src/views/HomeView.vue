<script setup>

import { RouterLink } from 'vue-router'
import Button from '../components/forms/Button.vue'

</script>

<template>
  <div>
    
    <div class="intro">
      <Transition name="slideUp" appear>
        <img class="logo" src="../assets/logo.svg" alt="Logo Griotte" />
      </Transition>
      <h1 class="title">Griotte</h1>
      <p class="subtitle">L'amie de vos <span>groceries</span>.</p>

    </div>
    <div class="buttons">
      <RouterLink class="btn btn--primary large" to="register">Inscription</RouterLink>
      <RouterLink class="btn btn--primary large" to="login">Connexion</RouterLink>
    </div>
  </div>
</template>

<style scoped lang="scss">
@import '@/assets/base.scss';
.intro {
  text-align: center;
  margin-top: 5rem;
  position: relative;
}

.intro:before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  width: 15rem;
  height: 15rem;
  background-color: #fff;
  z-index: -1;
}

.logo {
  width: 15rem;
  height: 15rem;
}

.title {
  font-size: 6rem;
  margin-top: 2rem;
  font-weight: 900;
  color: var(--color-primary);
  margin-top: 0;
  line-height: 1;
}

.subtitle {
  font-size: 2.1rem;
  font-weight: 300;
  font-weight: 700;
}

.subtitle span {
  color: var(--color-primary);
  font-weight: 700;
}

.buttons {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;

  @include respond-to('medium') {
    flex-direction: row;
  }
}

.buttons .btn {
  width: 15rem;
  font-weight: bold;
}

</style>