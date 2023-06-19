<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { watch, ref } from 'vue'
import NavMenu from './components/NavMenu.vue'

const route = useRoute()

// Watch the changes of the route name
watch(() => route.name, () => {

  // Set the className variable to the route name
  let className = route.name;

  // If the route has a class meta, add it to the className variable
  if(route.meta.class !== undefined) {
    className += ' ' + route.meta.class
  } 
  // Set the className variable to the body class
  document.body.className = className


})


</script>

<template>
    <header>
      <RouterLink class="logo" to="/">
        <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />
        <span>Griotte</span>
      </RouterLink>
      <!-- TODO : Hide if not logged in -->
      <NavMenu />
    </header>
    <main>
      <RouterView />
    </main>
</template>

<style scoped>

.logo {
  display: flex;
  align-items: center;
  line-height: 1;
  font-size: 1.7rem;
}

.logo img {
  margin-right: .5rem;
  transform: translateY(-9px);
}

.logo span {
  font-weight: 900;
}

header {
  padding: 1rem;
  background: var(--color-background);
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

</style>
