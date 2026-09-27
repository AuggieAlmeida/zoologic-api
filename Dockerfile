# Base image com PHP 8.2
FROM php:8.2-cli

# Atualiza o sistema e instala dependências essenciais
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    bash \
    && docker-php-ext-install zip pdo pdo_mysql opcache

# The built-in server runs under the CLI SAPI, where OPcache is off by default,
# so every request recompiled the router, controllers and vendor code. On a
# 0.1 vCPU instance that compile time is a visible share of each response.
# Timestamps are still validated so a mounted working tree reloads in
# development.
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=1'; \
      echo 'opcache.memory_consumption=64'; \
      echo 'opcache.max_accelerated_files=4000'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache-zoologic.ini

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho para a aplicação
WORKDIR /var/www/html

# Copia os arquivos do projeto para dentro do container
COPY . .

# Instala as dependências do Composer. The image never runs the tests (tests/
# is in .dockerignore), so PHPUnit stays out, and the authoritative classmap
# answers every class lookup without touching the filesystem.
RUN composer install --no-interaction --no-dev --classmap-authoritative

# Default port. The entrypoint binds $PORT when the platform assigns one.
EXPOSE 8000

# Copia o script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Define o entrypoint para o container
ENTRYPOINT ["docker-entrypoint.sh"]
