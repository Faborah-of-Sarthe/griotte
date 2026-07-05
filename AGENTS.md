# AGENTS.md

## Cursor Cloud specific instructions

Griotte est un monorepo à deux applications :

- `api/` : API Laravel 13 / PHP 8.3 (Fortify + Sanctum, admin Filament).
- `frontend/` : SPA/PWA Vue 3 + Vite.

### Environnement déjà en place (snapshot VM)

Le snapshot contient déjà PHP 8.3 + extensions, Composer, Node 22/npm, ainsi que
les fichiers `api/.env`, `frontend/.env` et la base SQLite `api/database/database.sqlite`
(migrations déjà appliquées). Le script de mise à jour ne fait que rafraîchir les
dépendances (`composer install` dans `api/`, `npm install` dans `frontend/`).

Points non évidents :

- **Base de données** : le dev utilise **SQLite** (`api/database/database.sqlite`), et non
  MySQL comme dans `api/.env.example`. Après une nouvelle migration, lancer
  `php artisan migrate` depuis `api/`. Pour repartir de zéro : `php artisan migrate:fresh`.
  Les tests utilisent SQLite en mémoire (voir `api/phpunit.xml`), indépendamment du `.env`.
- **Mail** : `MAIL_MAILER=log` dans `api/.env`. Aucun email n'est réellement envoyé ;
  les liens (vérification d'email, reset password) apparaissent dans
  `api/storage/logs/laravel.log`.

### Lancer les services (dev)

- API : depuis `api/`, `php artisan serve --host=0.0.0.0 --port=8000` (http://localhost:8000).
- Frontend : depuis `frontend/`, `npm run dev -- --host` (http://localhost:5173).
  Le `README` mentionne `npm run start`, qui n'existe pas : utiliser `npm run dev`.

Les deux doivent tourner ensemble : le frontend appelle l'API via `VITE_API_URL`/`VITE_AUTH_URL`,
et l'API n'autorise l'origine `localhost:5173` que via `SANCTUM_STATEFUL_DOMAINS` /
`CORS_ALLOWED_ORIGINS` (déjà configurés dans `api/.env`).

### Authentification / vérification d'email

- Les routes métier (`api/routes/api.php`) sont derrière `auth:sanctum` **et** `verified`.
  Un compte fraîchement inscrit doit donc être vérifié avant de créer un magasin, etc.
- Pour vérifier un compte en dev : soit ouvrir le lien de vérification présent dans
  `api/storage/logs/laravel.log`, soit forcer via tinker :
  `php artisan tinker --execute="App\Models\User::where('email','...')->update(['email_verified_at'=>now()]);"`.
- Après inscription, le frontend ne connecte pas automatiquement : passer par `/login`.

### Lint / tests / build

- API — lint : `./vendor/bin/pint` (le dépôt contient de nombreux écarts de style
  préexistants ; ne pas reformater sans demande). Tests : `php artisan test` (81 tests passent).
- Frontend — build : `npm run build`. Lint : `npm run lint` (ESLint remonte des erreurs
  `no-unused-vars` **préexistantes** dans `src/` ; ne pas corriger sans demande). Pas de tests unitaires.

### Docker

`docker-compose.yml` et `dockerfile` sont réservés à la production : ne pas les utiliser
ni les modifier pour le dev.
