<template>
    <nav :class="{'active' : isActive}">
        <div class="close" @click="toggle">
            <Cross></Cross>
        </div>
        <ul>
            <li><RouterLink to="my-list">Ma liste</RouterLink></li>
            <li><RouterLink to="#">Mes magasins</RouterLink></li>
            <li><RouterLink to="logout">Déconnexion</RouterLink></li>
        </ul>
    </nav>
    <div class="burger-icon" @click="toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
</template>

<script setup>

    import { RouterLink, useRoute } from 'vue-router'
    import Cross from './icons/Cross.vue'
    import { ref, watch, onMounted } from 'vue'


    const isActive = ref(false)

    // Toggle the menu
    const toggle = () => {
        isActive.value = !isActive.value
    }

    // On route change, close the menu
    const route = useRoute()
    watch(() => route.name, () => {
        isActive.value = false
    })

    // Close the menu on click outside
    const closeMenu = (e) => {
        if(!e.target.closest('nav') && isActive.value && e.target.closest('.burger-icon') === null) {
            isActive.value = false
        }
    }

    // Add the event listener on mount
    onMounted(() => {
        window.addEventListener('click', closeMenu)
    })
    

</script>

<style lang="scss" scoped>

    .burger-icon {
        cursor: pointer;
        span {
            display: block;
            width: 35px;
            height: 6px;
            background-color: var(--color-primary);
            margin: 5px;
            border-radius: 3px;
        }
    }

    .close {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        width: 1.5rem;
        height: 1.5rem;
        cursor: pointer;
    }

    nav {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 0;
        overflow-x: hidden;
        background: var(--color-background);
        padding-top: 5rem;
        color: var(--color-primary);
        box-shadow: 0 0 10px rgba(0,0,0,.1);
        transition: width .1s ease-in-out;
        z-index: 10;


        ul {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            height: 100%;

            li:last-child {
                margin-top: auto;
            }
        }

        a {
            font-size: 1.4rem;
            line-height: 2;
            white-space: nowrap;
        }
        &.active {
            width: 15rem;
            transition: width .3s ease-in-out;
        }
    }
    
    // TODO: handle media queries cross components
    @media screen and (orientation: landscape) {
        
        .burger-icon {
            display: none;
        }

        nav {
            position: static;
            width: auto;
            background: none;
            padding: 0;
            box-shadow: none;

            ul {
                display: flex;
                flex-direction: row;
                height: auto;
                padding: 0;

                li {
                    margin-left: 2rem;
                }
            }

            .close {
                display: none;
            }

        }

    }


  
    
</style>