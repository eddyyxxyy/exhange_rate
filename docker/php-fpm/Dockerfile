# php-fpm-custom/Dockerfile
FROM bitnami/php-fpm:8.4.8

# Instalar dependências de build, INCLUINDO 'build-essential'
RUN apt-get update && apt-get install -y \
    build-essential \
    pkg-config \
    libssl-dev \
    autoconf \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Instalar a extensão Redis via PECL
RUN pecl install redis \
    && echo "extension=redis.so" > /opt/bitnami/php/etc/conf.d/redis.ini

# Limpar as dependências de build após a instalação para manter a imagem menor
RUN apt-get purge -y --auto-remove \
    build-essential \
    pkg-config \
    libssl-dev \
    autoconf \
    gnupg \
    && rm -rf /var/lib/apt/lists/*