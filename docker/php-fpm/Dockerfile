FROM bitnami/php-fpm:8.4.8

# Install build dependencies, INCLUDING 'build-essential'
RUN apt-get update && apt-get install -y \
    build-essential \
    pkg-config \
    libssl-dev \
    autoconf \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Installs Redis extension via PECL
RUN pecl install redis \
    && echo "extension=redis.so" > /opt/bitnami/php/etc/conf.d/redis.ini

# Clean build deps, reducing the final image size
RUN apt-get purge -y --auto-remove \
    build-essential \
    pkg-config \
    libssl-dev \
    autoconf \
    gnupg \
    && rm -rf /var/lib/apt/lists/*