#!/bin/sh
set -e

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