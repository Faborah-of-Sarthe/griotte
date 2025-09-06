<template>
    <div class="input_wrapper" :class="props.type" v-if="props.type == 'option'">
        <label :for="name">
            <template v-if="label && !hideLabel">
                {{ label }}
            </template>
            <input type="checkbox" :id="name" ref="inputElement" :name="name" :checked="modelValue" @input="$emit('update:modelValue',  $event.target.checked)" v-bind="$attrs"/>
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
            required: false
        },

        modelValue: {
            type:  [String, Boolean],
            required: true,
            default: ''
        },
        hideLabel: {
            type: Boolean,
            required: false,
            default: false
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
    const name = props.label ? labelize(props.label) : props.label;
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