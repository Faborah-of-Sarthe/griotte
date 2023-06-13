<template>
    <button :type="type" class="btn" :class="'btn--' + design" :disabled="disabled">
        <slot></slot>
    </button>
</template>

<script setup>

import { defineProps } from 'vue'

const props = defineProps({
    design: {
        type: String,
        required: false,
        default: 'primary'
    },
    type: {
        type: String,
        required: false,
        default: 'submit'
    },
    disabled: {
        type: Boolean,
        required: false,
        default: false
    }
})


</script>

<style scoped>

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-size: 1.25rem;
    font-weight: bold;
    cursor: pointer;
    border: none;
    appearance: none;
}

.btn--primary {
    background-color: var(--color-primary);
    color: #fff;
    display: block;
    margin: auto;
}

</style>