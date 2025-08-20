<script setup>
import { ref, watch } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'
import BaseInput from './BaseInput.vue'

const props = defineProps({
    quantity: {
        type: [String, Number],
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Quantité'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    recipeId: {
        type: [String, Number],
        required: true
    },
    productId: {
        type: [String, Number],
        required: true
    }
})

const emit = defineEmits(['success', 'error'])

const queryClient = useQueryClient()
const localValue = ref(props.quantity)

const updateQuantityQuery = useMutation({
    mutationFn: (quantity) => {
        return axios.patch(import.meta.env.VITE_API_URL + 'recipes/' + props.recipeId + '/products/' + props.productId, {
            quantity
        })
    },
    onSuccess: () => {
        queryClient.invalidateQueries(['recipe', props.recipeId])
        emit('success')
    },
    onError: (error) => {
        emit('error', error)
    }
})

watch(() => props.quantity, (newValue) => {
    localValue.value = newValue
})

const handleBlur = (event) => {
    const value = event.target.value
    updateQuantityQuery.mutate(value)
}
</script>

<template>
    <div class="quantity-input-wrapper">
        <BaseInput 
            v-model="localValue"
            :placeholder="placeholder"
            :loading="updateQuantityQuery.isLoading.value"
            :disabled="disabled"
            class="quantity-input"
            @blur="handleBlur"
        />
    </div>
</template>

<style lang="scss" scoped>
.quantity-input-wrapper {
    width: 5rem;

    :deep(input) {
        width: 100%;
        font-size: 0.8rem;
        margin: 0;
    }
}
</style>
