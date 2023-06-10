<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
      <RouterLink to="/">
        <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />
      </RouterLink>
      <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="my-list">Ma liste</RouterLink>
      </nav>
  </header>
  <RouterView />
</template>

<style scoped>

</style>
