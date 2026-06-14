@props([
    'value' => null,
    'masked_value' => null,
])

<span
    x-data="{ revealed: false }"
    class="inline-flex items-center gap-2"
>
    <span x-show="! revealed" x-cloak>{{ $masked_value }}</span>
    <span x-show="revealed" x-cloak>{{ $value ?? 'Non renseigne' }}</span>

    @if (filled($value))
        <button
            type="button"
            x-on:pointerdown.stop
            x-on:click.prevent.stop="$event.stopImmediatePropagation(); revealed = ! revealed"
            class="inline-flex text-primary-600 transition hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            x-bind:aria-label="revealed ? 'Masquer la valeur' : 'Afficher la valeur'"
        >
            <svg
                x-show="! revealed"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="h-4 w-4"
                style="width: 1rem; height: 1rem;"
                aria-hidden="true"
            >
                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                <path
                    fill-rule="evenodd"
                    d="M.664 10.59a1.651 1.651 0 0 1 0-1.18C1.84 6.486 5.186 3.75 10 3.75s8.16 2.736 9.336 5.66c.15.373.15.787 0 1.18C18.16 13.514 14.814 16.25 10 16.25S1.84 13.514.664 10.59ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                    clip-rule="evenodd"
                />
            </svg>
            <svg
                x-show="revealed"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="h-4 w-4"
                style="display: none; width: 1rem; height: 1rem;"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-2.096-2.096c1.477-1.076 2.488-2.449 3.047-3.756a2.25 2.25 0 0 0 0-1.736C17.56 6.391 14.204 3.75 10 3.75c-1.516 0-2.885.343-4.049.915L3.28 2.22Zm5.236 5.236 1.034 1.034a1.5 1.5 0 0 1 1.96 1.96l1.034 1.034A3 3 0 0 0 8.516 7.456Z"
                    clip-rule="evenodd"
                />
                <path d="m12.28 15.043-1.803-1.803a3 3 0 0 1-3.717-3.717L4.979 7.742c-1.047.884-1.76 1.969-2.105 2.79a.75.75 0 0 0 0 .936C3.885 13.878 6.633 16.25 10 16.25c.793 0 1.557-.107 2.28-.307Z" />
            </svg>
        </button>
    @endif
</span>
