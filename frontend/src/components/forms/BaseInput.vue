<template>
    <label v-if="label" :for="name">
      {{ label }}
    </label>
    <input :id="name"  :name="name" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" v-bind="$attrs"/>
</template>

<script setup>

  import { defineProps } from 'vue'

  const props = defineProps({
    label: {
      type: [String, Boolean],
      required: true
    },

    modelValue: {
      type: String,
      required: true,
      default: ''
    },
  })

  // Create name from label
  const name = props.label.toLowerCase().replace(' ', '-');

  // TODO: Handle password visualization toggle

</script>

<style scoped>

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
  }

  input {
    display: block;
    width: 100%;
    padding: 0.5rem;
    border: 2px solid #b2b2b2;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    appearance: none;
  }
  input:focus {
    outline: none;
    border-color: var(--color-primary);
  }
  

</style>