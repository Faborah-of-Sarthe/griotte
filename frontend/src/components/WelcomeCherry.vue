<template>
    <div v-if="step" class="dialog-wrapper">
        <div class="dialog-cherry">
            <p v-if="step == 'store'">
                Bonjour et bienvenue sur Griotte ! <br>
                Mon but est de vous aider à gagner du temps durant vos courses, en ayant une liste de courses qui se range automatiquement selon votre magasin.<br><br>
                Pour cela, commençons par créer notre premier magasin en cliquant sur le bouton ci-dessous.
            </p>
        </div>
        <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />

    </div>
</template>
<style scoped>

    .dialog-cherry {
        background: var(--color-1-light);
        color: var(--dialog-text);
        border-radius: 10px;
        padding: 1rem;
        position: relative;
        margin-bottom: .5rem;
    }
    
    .dialog-cherry::before {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 50px;
        transform: translateX(-50%);
        border: 10px solid transparent;
        border-top-color: var(--color-1-light);
    }


</style>
<script setup>


// Props
const props = defineProps({
    step: {
        type: String,
        required: true
    },
  
})

</script>