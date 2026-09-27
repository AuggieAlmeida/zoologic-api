#!/bin/sh
set -e

# The .env file only exists in local development. On a managed platform the
# configuration arrives as real environment variables, so sourcing it
# unconditionally under `set -e` would kill the container on boot.
if [ -f /var/www/html/.env ]; then
    set -a
    . /var/www/html/.env
    set +a
    echo ".env carregado!"
else
    echo ".env ausente: usando as variáveis do ambiente."
fi

# Platforms assign the port and expect the process to bind it.
: "${PORT:=8000}"

# Waiting for the database and creating the schema both live in migrate.php,
# which reuses the application DSN, port and TLS settings. It exits non-zero on
# failure, and `set -e` turns that into a failed boot instead of a server
# answering every request with a database error.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Rodando migrações..."
    php migrate.php
else
    echo "RUN_MIGRATIONS desativado: pulando as migrações."
fi

# The built-in server handles one request at a time unless it is told to fork
# workers. With a single process a 600 ms login (bcrypt) stalled every other
# request queued behind it, and the three calls the dashboard fires together
# ran one after the other.
: "${PHP_CLI_SERVER_WORKERS:=4}"
export PHP_CLI_SERVER_WORKERS

echo "Iniciando servidor PHP na porta ${PORT} com ${PHP_CLI_SERVER_WORKERS} workers..."
exec php -S "0.0.0.0:${PORT}" -t public
