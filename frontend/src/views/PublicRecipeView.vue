<script setup>
import { useRoute } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import axios from 'axios'
import Loader from '../components/Loader.vue'
import ExternalLink from '../components/icons/ExternalLink.vue'
import MarkdownContent from '../components/MarkdownContent.vue'
import { computed } from 'vue'

const route = useRoute()

const token = computed(() => route.params.token)

const { data: recipe, isLoading, isError } = useQuery({
    queryKey: computed(() => ['public-recipe', token.value]),
    queryFn: async () => {
        const res = await axios.get(import.meta.env.VITE_API_URL + 'public/recipes/' + token.value)
        return res.data.data
    },
    enabled: computed(() => !!token.value),
    retry: false
})
</script>

<template>
    <div>
        <div v-if="isLoading">
            <Loader loadingText="Chargement de la recette..." />
        </div>

        <div v-else-if="isError || !recipe" class="not-found">
            <p>Cette recette n'existe pas ou n'est plus partagée publiquement.</p>
        </div>

        <div v-else class="recipe-detail">
            <div class="header">
                <h1>
                    {{ recipe.name }}
                    <a v-if="recipe.link"
                       :href="recipe.link"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="external-link-icon"
                       title="Ouvrir le lien de la recette dans un nouvel onglet">
                        <ExternalLink />
                    </a>
                </h1>
            </div>

            <div v-if="recipe.tags && recipe.tags.length > 0" class="tags">
                <span v-for="(tag, index) in recipe.tags" :key="index" class="tag">
                    {{ tag.name }}
                </span>
            </div>

            <div class="ingredients">
                <h2>Ingrédients</h2>
                <div v-if="!recipe.products || recipe.products.length === 0" class="no-ingredients">
                    <p class="alert-info">Aucun ingrédient n'a été ajouté à cette recette.</p>
                </div>
                <ul v-else class="ingredients-list">
                    <li v-for="(product, index) in recipe.products" :key="index" class="ingredient-item">
                        <span class="ingredient-name">
                            {{ product.name }}
                            <span v-if="product.quantity" class="quantity">
                                ({{ product.quantity }})
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div v-if="recipe.description" class="description">
                <h2>Description</h2>
                <MarkdownContent :source="recipe.description" />
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.recipe-detail {
    padding: 1rem;
}

h2 {
    color: var(--color-text-alt);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;

    h1 {
        margin: 0;
        flex: 1;
        min-width: 250px;
        display: flex;
        align-items: baseline;
        gap: 0.75rem;
    }

    .external-link-icon {
        color: var(--color-primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;

        svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        &:hover {
            opacity: 0.8;
        }
    }
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;

    .tag {
        background: var(--color-1-light);
        border-radius: 0.375rem;
        color: var(--color-primary);
        font-size: 0.875rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
    }
}

.description {
    margin-bottom: 2rem;

    h2 {
        margin-bottom: 1rem;
    }
}

.ingredients {
    margin-bottom: 2rem;

    .ingredients-list {
        padding: 0;
        margin: 0;
        padding-top: 1rem;

        .ingredient-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;

            .ingredient-name {
                flex: 1;
                font-size: 1.1rem;

                .quantity {
                    color: var(--color-text-alt);
                }
            }
        }
    }
}

.not-found {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--color-text-alt);
}
</style>
