<template>
    
    <div class="icons-list">
        <template v-for="(iconName, index) in iconNames" :key="iconName">
            <Transition name="slideUp" appear>
                <div :style="{ 'transition-delay': index * 50 + 'ms'}" class="icon-wrapper" :class="{selected: iconName == value}"  @click="handleChange(iconName)">
                    <SectionIcon color="0" class="big" :icon="iconName"></SectionIcon>
                </div>
            </Transition>
        </template>
    </div>

</template>

<script setup>

import { defineProps, defineEmits } from 'vue'
import SectionIcon from '@/components/SectionIcon.vue'
const props = defineProps(['value', 'icon', 'modelValue'])
const emit = defineEmits(['update:modelValue'])

//parse all the svg icons in the folder assets/icons
const icons = import.meta.glob('../section-icons/*.vue')
// // For each icon, get the name without the extension
const iconNames = Object.keys(icons).map((icon) => {
    return icon.replace('../section-icons/', '').replace('.vue', '')
})


const handleChange = (iconName) => {
    emit('update:modelValue', iconName)
}

</script>

<style scoped>

.icon-wrapper {
    cursor: pointer;
    width: calc(100% / 3);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;


}
.selected {
    transition: all .1s ease;
    background-color: var(--color-1-light);
}
.icons-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    max-height: 60vh;
    overflow-y: scroll;
}

</style>