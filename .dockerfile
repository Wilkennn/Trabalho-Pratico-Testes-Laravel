FROM php:8.2-fpm

# Instala apenas as dependências do sistema que são estritamente necessárias
# para as extensões do PHP e para o Composer.
# Note que não usamos 'apt-get upgrade'.
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# Instala as extensões do PHP que o Laravel precisa.
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala o Composer (gerenciador de pacotes do PHP) globalmente.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho padrão.
WORKDIR /var/www

# Expõe a porta 9000 para o serviço php-fpm.
EXPOSE 9000
CMD ["php-fpm"]

