<template>
    <label v-if="label" :for="name">
      {{ label }}
    </label>
    <div class="input_wrapper">
        <textarea :id="name" :name="name" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" v-bind="$attrs" cols="30" rows="10">
            
        </textarea>
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
      type: String,
      required: true,
      default: ''
    },
  })

  // Create name from label
  const name = labelize(props.label);
</script>

<style scoped>

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
  }

  textarea {
    display: block;
    width: 100%;
    padding: 0.5rem;
    border: 2px solid #b2b2b2;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    outline:none;
  }

  textarea:focus {
    border-color: var(--color-primary);
  }

  .input_wrapper {
    position: relative;
  }

</style>