<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
    <h1>
      <RouterLink to="/">
        <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />
        <span>Griotte</span>
      </RouterLink>
    </h1>
    <nav>
      <RouterLink to="/">Home</RouterLink>
      <RouterLink to="my-list">Ma liste</RouterLink>
    </nav>
  </header>
  <RouterView />
</template>

<style scoped>

h1 a {
  display: flex;
  align-items: center;
  line-height: 1;
}

h1 img {
  margin-right: .5rem;
  transform: translateY(-9px);
}

h1 span {
  font-weight: 900;
}

</style>
