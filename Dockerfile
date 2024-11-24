FROM php:8.2-cli

# Atualiza o sistema e instala dependências
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala as dependências do Composer
RUN composer install --no-interaction --optimize-autoloader

# Adiciona o script de entrypoint e dá permissão de execução
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe a porta 8000
EXPOSE 8000

# Define o entrypoint
ENTRYPOINT ["docker-entrypoint.sh"] 