<template>
    <div class="icon" v-bind="$attrs" :class="'bg-color-' +  color ">
        <component v-if="icon" :is="icon" />
    </div>
</template>

<script setup>

import { computed, defineAsyncComponent } from 'vue'

// Props
const props = defineProps({
    color: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        required: false,
        default: ''
    },
})

const icons = import.meta.glob('@/assets/icons/*.vue')
const icon = computed(() => {
    if(props.icon) {
        return defineAsyncComponent(() => import('/src/assets/icons/' + props.icon + '.vue'))
    } else {
       return false;
    }
})

</script>

<style scoped>
.icon {
  
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.icon svg {
    width: 80%;
    height: 80%;
}

.big {
    width: 4rem;
    height: 4rem;
}

.medium {
    width: 3rem;
    height: 3rem;
}

.small {
    width: 2rem;
    height: 2rem;
}


</style>