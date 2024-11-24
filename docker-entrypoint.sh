#!/bin/sh
set -e

# Cria o arquivo .env a partir das variáveis do ambiente
cat > /var/www/html/.env <<EOL
DB_HOST=${DB_HOST}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
DB_PORT=${DB_PORT}
EOL

echo ".env criado dinamicamente!"
exec "$@"


# Aguarda o MySQL estar pronto
echo "Aguardando MySQL..."
while ! php -r "
try {
    new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}');
    echo 'MySQL conectado!\n';
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
"
do
    sleep 1
done

# Roda as migrações
echo "Rodando migrações..."
php migrate.php

# Inicia o servidor PHP
echo "Iniciando servidor PHP..."
exec php -S 0.0.0.0:8000 -t public 