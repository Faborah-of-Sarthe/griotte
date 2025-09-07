FROM webdevops/php-apache:8.2-alpine

# Installation dans votre Image du minimum pour que Docker fonctionne
RUN apk add oniguruma-dev libxml2-dev nano ca-certificates
RUN docker-php-ext-install \
        bcmath \
        ctype \
        mbstring \
        pdo_mysql \
        xml

# Installation dans votre image de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN update-ca-certificates

# Installation dans votre image de NodeJS
RUN apk add nodejs npm

ENV WEB_DOCUMENT_ROOT /app
ENV APP_ENV production
WORKDIR /app
COPY . .

WORKDIR api



# Installation et configuration de votre site pour la production
# https://laravel.com/docs/10.x/deployment#optimizing-configuration-loading
RUN composer install --no-interaction --optimize-autoloader --no-dev
# Generate security key
RUN php artisan key:generate
# Optimizing Configuration loading
RUN php artisan config:cache
# Optimizing Route loading
RUN php artisan route:cache
# Optimizing View loading
RUN php artisan view:cache

WORKDIR ../frontend
# Compilation des assets de Breeze (ou de votre site)
RUN npm install
RUN npm run build

WORKDIR /app
RUN chown -R application:application .
# Forcer les permissions sur le dossier api
RUN chown -R application:application api/
RUN chmod -R 775 api/storage api/bootstrap/cache
