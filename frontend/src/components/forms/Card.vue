<template>
    
<div class="card" :class="variation">
    <h2>{{ title }}</h2>
    <div>
        <slot></slot>
    </div>
</div>

</template>

<script setup>

import { defineProps } from 'vue'

const props = defineProps({
    variation: {
        type: String,
        required: false,
        default: ''
    },
    title: {
        type: String,
        required: false,
        default: ''
    }
})


</script>

<style scoped>
.card h2{
    margin-bottom: 1rem;
}

:deep(.btn) {
    margin-top: 2rem;
}
.card {
    width: 100%;
    max-width: 400px;
    padding: 1rem;
    border-radius: 0.5rem;
    background-color: var(--color-background);
}
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 30rem;
    background: var(--color-background);
    border-radius: .5rem;
    padding: 1rem;
    z-index: 25;

}
.card--loading > *{
    opacity: 0.5;
}

</style>