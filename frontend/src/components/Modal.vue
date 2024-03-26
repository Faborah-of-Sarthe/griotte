<template>
    <Transition name="slideUp" appear v-bind="$attrs">
        <Card :title="title">
            <slot name="content"></slot>
            <div v-if="buttons" class="buttons">
                <Button type="button" @click="$emit('close')" class="btn btn--secondary">Annuler</Button>
                <Button type="button" @click="$emit('validate')" class="btn btn--primary">Supprimer</Button>
            </div>
        </Card>
    </Transition>
    <Transition name="fadeIn" appear>
        <div class="background-overlay" @click="$emit('close')"></div>
    </Transition>
</template>

<script setup>
    
import Card  from '@/components/forms/Card.vue'
import Button from '@/components/forms/Button.vue'

const props = defineProps({
    title: String,
    buttons: Boolean
})


</script>   
