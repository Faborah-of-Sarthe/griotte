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

button:disabled{
    background-color: var(--color-9);
    color: var(--color-text);
}

</style>