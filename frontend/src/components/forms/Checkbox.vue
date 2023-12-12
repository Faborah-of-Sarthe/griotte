<template>
    <div class="input_wrapper" :class="props.type" v-if="props.type == 'option'">
        <label v-if="label" :for="name">
            {{ label }}
            <input type="checkbox" :id="name" ref="inputElement" :name="name" :value="modelValue" @input="$emit('update:modelValue',  $event.target.checked)" v-bind="$attrs"/>
            <CheckMark :class="{checked: modelValue}"  class="checkbox"></CheckMark>
        </label>
    </div>
    <!-- TODO : Handle list of checkbox -->
</template>
<script setup>

    import { defineProps, ref} from 'vue'
    import { labelize } from '../../utils';
    import CheckMark from '../icons/CheckMark.vue';


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

        type: {
            type: String,
            required: false,
            default: 'option',
            validator: (value) => {
                return ['option', 'list'].includes(value)
            }
        }
    })

    // Create name from label
    const name = labelize(props.label);
</script>

<style>

.option input {
    display: none;
}
.option label {
    display: flex;
    cursor: pointer;
    align-items: center;
}

.option .checkmark {
    flex-shrink: 0;
}


</style>