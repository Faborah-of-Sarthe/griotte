<template>
    <div v-if="step" class="dialog-wrapper">
        <div class="dialog-cherry">
            <p v-if="step == 'store'">
                Bonjour et bienvenue sur Griotte ! <br>
                Mon but est de vous aider à gagner du temps durant vos courses, en ayant une liste de courses qui se range automatiquement selon vos magasins.<br><br>
                Pour cela, commençons par créer notre premier magasin en cliquant sur le bouton ci-dessous.
            </p>
            <p v-else-if="step == 'section'">
                Parfait !  Si besoin de modifier le nom du magasin, cliquez simplement sur son nom. <br>
                Maintenant, on va pouvoir créer les rayons de votre magasin. <br>
                Pour vous aider à vous y retrouver, vous pouvez même choisir une icône et une couleur pour chaque rayon. <br> 
                Essayez de créer un rayon "Boulangerie" par exemple, en cliquant sur le bouton ci-dessous ! 
            </p>
            <p v-else-if="step == 'section2'">
                Vous avez créé votre premier rayon ! <br>
                Je vous ai au passage ajouté un rayon Fruits & Légumes, c'est plus pratique pour trouver des cerises ! <br><br>
                Pour optimiser vos trajets dans le magasin, vous pouvez réorganiser vos rayons en les faisant glisser. <br><br>
                Sinon vous pouvez accéder à votre liste de courses en cliquant sur le bouton ci-dessous !
            </p>
            <p v-else-if="step == 'product'">
                Top ! Je me suis permis d'ajouter un 1er produit à la liste ;) <br>
                En cliquant dessus, vous pouvez voir les commentaires qui y sont liés (quantité, plat...). <br>
                En appuyant longuement sur un produit, vous pouvez modifier son nom ou son commentaire mais aussi l'assigner à un autre rayon !<br><br>
                Enfin, vous pouvez ajouter un produit à votre liste en cliquant sur le gros bouton <Cross class="plus" />. Si je ne le connais pas, vous pourrez renseigner ses informations et son rayon, il sera classé automatiquement la prochaine fois ! <br><br>
                Je vous ai déjà ajouté une bonne partie des fruits et légumes, vous pouvez essayer d'en chercher ou d'en ajouter un nouveau ! Bonnes courses !
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
    img {
        animation: shake 15s linear infinite;

    }
    .plus {
        height: .7rem;
        width: .7rem;
        transform: rotate(45deg);

    
    }



</style>
<script setup>
import Cross from './icons/Cross.vue';



// Props
const props = defineProps({
    step: {
        type: String,
        required: true
    },
  
})

</script>