<template>
    <div class="color-select">
        <label v-if="label">
            {{ label }}
        </label>
        <div class="colors">
            <template v-for="index in 9" :key="index">
                <Transition name="slideUp" appear>
                    <div :style="{ 'transition-delay':index * 50 + 'ms' }" 
                    class="section" 
                    :class="{selected: index == value}"
                    @click="handleChange(index)">
                        <SectionIcon class="big" :color="index"></SectionIcon>
                    </div>
                </Transition>
            </template>
        </div>
    </div>
</template>

<script setup>

import { defineProps} from 'vue'
import SectionIcon from '@/components/SectionIcon.vue'

const props = defineProps({
    label: {
        type: [String, Boolean],
        required: true
    },
    value:  {
        type: Number,
        required: false,
        default: 0
    },

    modelValue: {
        type: Number,
        required: true,
        default: ''
    },
})


const emit = defineEmits(['update:modelValue'])

// Emit the selected color
const handleChange = (number) => {
    const color =  parseInt(number)
    emit('update:modelValue', color)
}


</script>

<style scoped>
label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
} 
.section
{
    width: calc(100% / 3);
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.2s ease-in-out;
    padding: 1rem 0.5rem;
    border-radius: 0.5rem;
} 
.section.selected {
    transition: all .1s ease;
    background-color: var(--color-1-light);
}

.colors  {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    margin-top: 1rem;
}
  
</style>