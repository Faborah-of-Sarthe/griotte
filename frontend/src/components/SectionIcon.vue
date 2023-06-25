<template>
    <div class="icon" v-bind="$attrs" :class="'bg-color-' +  color "></div>
</template>

<script setup>

// Props
const props = defineProps({
  color: {
    type: [String, Number],
    required: true
  }
})

</script>

<style scoped>
.icon {
  
    border-radius: 50%;
}

.big {
    width: 4rem;
    height: 4rem;
}

.medium {
    width: 3rem;
    height: 3rem;
}

.small {
    width: 2rem;
    height: 2rem;
}


</style>