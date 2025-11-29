FROM mirror.gcr.io/library/php:8.1-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar a extensão gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instalar outras extensões PHP
RUN docker-php-ext-install pdo_mysql zip

# Instalar Composer
COPY --from=mirror.gcr.io/library/composer /usr/bin/composer /usr/bin/composer

# Configurar o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos da aplicação
COPY . .

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader

# Alterar UID e GID do www-data para corresponder ao usuário do host
RUN usermod -u 1001 www-data && groupmod -g 1001 www-data

# Copiar a configuração personalizada do Apache para substituir o site padrão
COPY apache-custom.conf /etc/apache2/sites-available/000-default.conf

# Expor a porta
EXPOSE 89
