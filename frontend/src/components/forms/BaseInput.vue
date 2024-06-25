<template>
    <label v-if="label" :for="name">
      {{ label }}
    </label>
    <div class="input_wrapper">
      <input :id="name" ref="inputElement" :name="name" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" v-bind="$attrs"/>
      <span @click="toggleEye" :class="passwordClass" class="eye" v-if="$attrs.type == 'password'">
        <component :is="passwordClass == 'closed' ? Eye : EyeOff" />
      </span>
    </div>
</template>

<script setup>

  import { defineProps, ref} from 'vue'
  import { labelize } from '../../utils';
  import Eye from '../icons/Eye.vue';
  import EyeOff from '../icons/EyeOff.vue';




  const passwordClass = ref('closed')
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

  // Change type of input
  const toggleEye = () => {
    passwordClass.value = passwordClass.value == 'closed' ? 'open' : 'closed'
    inputElement.value.type = inputElement.value.type == 'password' ? 'text' : 'password'
  }
  

</script>

<style scoped>



  input {
    display: block;
    width: 100%;
    padding: 0.5rem;
    border: 2px solid #b2b2b2;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    appearance: none;
    background: transparent;
  }
  input:focus {
    outline: none;
    border-color: var(--color-primary);
  }

  .input_wrapper {
    position: relative;
  }

  .eye {
    position: absolute;
    right: 0.5rem;
    top: 0.5rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: #b2b2b2;
    height: 1.5rem;
    width: 1.5rem;

  }

  .password {
    padding-right: 2.5rem;
  }
  

</style>