# Base image com PHP 8.2
FROM php:8.2-cli

# Atualiza o sistema e instala dependências essenciais
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    bash \
    && docker-php-ext-install zip pdo pdo_mysql

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho para a aplicação
WORKDIR /var/www/html

# Copia os arquivos do projeto para dentro do container
COPY . .

# Instala as dependências do Composer
RUN composer install --no-interaction --optimize-autoloader

# Default port. The entrypoint binds $PORT when the platform assigns one.
EXPOSE 8000

# Copia o script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Define o entrypoint para o container
ENTRYPOINT ["docker-entrypoint.sh"]
