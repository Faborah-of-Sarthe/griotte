#!/bin/bash
# Script de déploiement automatisé de Griotte.
# À lancer depuis la racine du dépôt sur le serveur, avec l'utilisateur applicatif.
# Toutes les commandes PHP/Composer sont exécutées DANS le container web,
# ce qui évite le "sudo su" et la connexion manuelle au container.
set -euo pipefail

# Se placer dans le dossier du script (racine du dépôt), quel que soit l'appelant.
cd "$(dirname "$0")"

# Nom du container web défini dans docker-compose.yml.
container="griotte_web"

# Si l'utilisateur n'est pas dans le groupe "docker", on préfixe par sudo.
# Astuce : "sudo usermod -aG docker $USER" (puis reconnexion) supprime ce besoin.
if docker info >/dev/null 2>&1; then
    docker_cmd="docker"
else
    docker_cmd="sudo docker"
fi

# Exécute une commande dans le dossier api/ du container, en tant qu'utilisateur applicatif.
run_in_container() {
    $docker_cmd exec -w /app/api "$container" "$@"
}

echo "Déploiement démarré ..."

# Récupérer la dernière version du code (met à jour ./api et ./frontend via les volumes).
git pull

# Passer en mode maintenance (ou ne rien faire si déjà en maintenance).
run_in_container php artisan down || true

# Installer les dépendances PHP de production.
run_in_container composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Recompiler les assets du frontend (le dossier dist/ est monté dans le container).
( cd frontend && npm run build )

# Vider puis régénérer le cache.
run_in_container php artisan clear-compiled
run_in_container php artisan optimize

# Lancer les migrations (--force car environnement de production, sans confirmation).
run_in_container php artisan migrate --force

# Sortir du mode maintenance.
run_in_container php artisan up

echo "Déploiement terminé !"
