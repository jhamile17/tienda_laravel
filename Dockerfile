FROM php:8.2-cli

# Instalar Node.js + dependencias sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

# 🔥 INSTALAR NODE + NPM
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 🔥 FRONTEND BUILD (IMPORTANTE)
COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npm run build

RUN ls -la public/build

# permisos
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
