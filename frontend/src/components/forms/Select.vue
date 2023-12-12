<template>
    <label v-if="label" :for="name">
      {{ label }}
    </label>
    <div class="input_wrapper">
        <select :id="name" ref="inputElement" :name="name" :value="modelValue" @input="$emit('update:modelValue',  $event.target.value)" v-bind="$attrs">
            <option v-for="option in options" :value="option.value">{{ option.name }}</option>
        </select>
    </div>
</template>

<script setup>

    import { defineProps, ref} from 'vue'
    import { labelize } from '../../utils';

    const inputElement = ref();

    const props = defineProps({
        label: {
            type: [String, Boolean],
            required: true
        },

        modelValue: {
            type:  [String, Boolean],
            required: true,
            default: ''
        },

        options: {
            type: Array,
            required: true
        }
    })

    // Create name from label
    const name = labelize(props.label);
</script>

<style scoped>

    select {
        display: block;
        width: 100%;
        padding: 0.5rem;
        border: 2px solid #b2b2b2;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        outline:none;
        background: transparent;
    }

    select:focus {
        border-color: var(--color-primary);
    }

    .input_wrapper {
        position: relative;
    }

</style>