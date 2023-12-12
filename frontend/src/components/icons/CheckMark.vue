<template>
    <svg class="checkmark"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50"><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
</template>
<style scoped>
    .checkmark {
        stroke: var(--color-text);
        border: 2px solid var(--color-text);
        width: 1.5rem;
        height: 1.5rem;
        border-radius: .25rem;
        display: block;
        stroke-width: 4px;
        stroke-dashoffset: 50;
        stroke-dasharray: 50;
        transition: background-color 0.2s ease-in-out, stroke-dashoffset 0.3s ease-in-out;
        cursor: pointer;
        margin-left: auto;
    }
    .checked.checkmark,
    .current .checkmark {
        stroke-dashoffset: 0;
    }

</style>