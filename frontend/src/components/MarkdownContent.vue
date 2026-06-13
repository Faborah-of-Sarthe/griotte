<template>
    <div class="markdown-body" v-html="html"></div>
</template>

<script setup>
import { computed } from 'vue'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

const props = defineProps({
    source: {
        type: String,
        default: ''
    }
})

const allowedTags = [
    'a',
    'br',
    'em',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'li',
    'ol',
    'p',
    'strong',
    'ul'
]

const html = computed(() => {
    const rawHtml = marked.parse(props.source ?? '', {
        breaks: true,
        gfm: true
    })

    return DOMPurify.sanitize(rawHtml, {
        ALLOWED_TAGS: allowedTags,
        ALLOWED_ATTR: ['href', 'title']
    })
})
</script>

<style lang="scss" scoped>
.markdown-body {
    line-height: 1.6;

    :deep(*) {
        overflow-wrap: anywhere;
    }

    :deep(p),
    :deep(ul),
    :deep(ol) {
        margin: 0 0 1rem;
    }

    :deep(p:last-child),
    :deep(ul:last-child),
    :deep(ol:last-child) {
        margin-bottom: 0;
    }

    :deep(h1),
    :deep(h2),
    :deep(h3),
    :deep(h4),
    :deep(h5),
    :deep(h6) {
        color: var(--color-text);
        line-height: 1.3;
        margin: 1rem 0 0.5rem;
    }

    :deep(h1:first-child),
    :deep(h2:first-child),
    :deep(h3:first-child),
    :deep(h4:first-child),
    :deep(h5:first-child),
    :deep(h6:first-child) {
        margin-top: 0;
    }

    :deep(ul),
    :deep(ol) {
        padding-left: 1.5rem;
    }

    :deep(ul) {
        list-style-type: disc;
    }

    :deep(ol) {
        list-style-type: decimal;
    }

    :deep(ul li) {
        list-style-type: disc;
    }

    :deep(ol li) {
        list-style-type: decimal;
    }

    :deep(strong) {
        font-weight: 700;
    }

    :deep(em) {
        font-style: italic;
    }

    :deep(a) {
        color: var(--color-primary);
        text-decoration: underline;
    }
}
</style>
