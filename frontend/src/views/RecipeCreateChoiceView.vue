<script setup>
import { useRouter } from 'vue-router'
import Button from '../components/forms/Button.vue'
import { ref } from 'vue'

const router = useRouter()

const navigateToManualCreation = () => {
    router.push({ name: 'create-recipe-manual' })
}

const navigateToImportCreation = () => {
    router.push({ name: 'import-recipe' })
}
</script>

<template>
    <div class="choice-container">
        <h1>Créer une recette</h1>
        <p class="subtitle">Comment souhaitez-vous créer votre recette ?</p>
        
        <div class="choice-cards">
            <div class="choice-card" @click="navigateToImportCreation">
                <div class="choice-icon">🌐</div>
                <h3>Importer depuis un site</h3>
                <p>Copiez le lien d'une recette depuis votre site de cuisine préféré et laissez Griotte extraire automatiquement les informations.</p>
                <Button design="primary" type="button">
                    Importer depuis un lien
                </Button>
            </div>
            
            <div class="choice-card" @click="navigateToManualCreation">
                <div class="choice-icon">✏️</div>
                <h3>Créer manuellement</h3>
                <p>Créez votre recette de A à Z en remplissant vous-même tous les détails.</p>
                <Button design="secondary" type="button">
                    Créer manuellement
                </Button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.choice-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
    text-align: center;
}

.subtitle {
    font-size: 1.125rem;
    color: var(--color-text-secondary, #666);
    margin-bottom: 3rem;
}

.choice-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.choice-card {
    background: var(--color-background-soft, #f8f9fa);
    border: 2px solid var(--color-border, #e5e7eb);
    border-radius: 1rem;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
        border-color: var(--color-primary, #ef4444);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
}

.choice-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.choice-card h3 {
    margin: 1rem 0;
    color: var(--color-heading, #1f2937);
}

.choice-card p {
    color: var(--color-text-secondary, #666);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .choice-container {
        padding: 1rem;
    }
    
    .choice-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .choice-card {
        padding: 1.5rem;
    }
}
</style>

