FROM php:8.2-fpm

# Argumentos (opcionalmente definidos no docker-compose.yml)
ARG user=laravel
ARG uid=1000

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nano \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Obter Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário Laravel
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

# Expor porta da aplicação
EXPOSE 8000

# Comando padrão (servidor do Laravel)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]