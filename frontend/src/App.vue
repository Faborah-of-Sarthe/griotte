<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { watch, ref } from 'vue'

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
      <h1>
        <RouterLink to="/">
          <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />
          <span>Griotte</span>
        </RouterLink>
      </h1>
      <nav v-if="false">
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="my-list">Ma liste</RouterLink>
      </nav>
    </header>
    <main>
      <RouterView />
    </main>
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

header {
  padding: 1rem;
  background: var(--color-background);
  margin-bottom: 2rem;
}

</style>
