<template>
    <div class="loader">
        <img alt="Logo" src="@/assets/logo.svg" width="50" height="50" />
        <div class="loader_text">Chargement...</div>
    </div>
</template>

<style scoped>
    .loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 50vh;
        font-size: 1.5rem;
    }
    .loader_text {
        margin-top: 1rem;
        color: var(--color-text-alt);
        animation: blink 2s linear infinite;
    }
    @keyframes blink {
        0%, 100% {
            opacity: 0.5;
        }
        50% {
            opacity: 1;
        }
    }
    img {
        animation: shake 2s linear infinite;
        filter: grayscale(100%);
    }
    @keyframes shake {
      
        0%,100% {
            transform: translateX(-10px) rotate(-10deg);
        }
        50% {
            transform: translateX(10px) rotate(10deg);
        }
   
    }
</style>