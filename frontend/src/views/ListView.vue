
<script setup>

import { useQuery } from '@tanstack/vue-query'

// Get sections and products from API
const { isLoading, isError, data, error } = useQuery({
  queryKey:  ['products'],
  queryFn: () => {
    return fetch(import.meta.env.VITE_API_URL + 'products').then((res) =>
      res.json()
    )
  }
})

</script>
<template>
  <div class="about">

  </div>
</template>

<style>

</style>