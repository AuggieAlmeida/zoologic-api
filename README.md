# ZooLogic API

REST API for ZooLogic, a zoo management system. It covers animals, habitats, veterinarians, staff and the users who operate the panel. It is plain PHP 8 with a small hand-written router and an MVC layout, MySQL through PDO and JWT authentication.

- **Live API:** https://zoologic-api.onrender.com/api/health
- **Front end:** [zoologic-front](https://github.com/AuggieAlmeida/zoologic-front), live at https://zoologic-front.vercel.app

> The API runs on Render's free plan, which sleeps when idle. The first request after a quiet period can take about a minute. Requests after that are fast.

## Stack

| Layer | Choice |
|---|---|
| Language | PHP 8.2 (runs on `>=8.0`) |
| HTTP | PHP built-in server, custom router (`app/Routes/Router.php`) |
| Database | MySQL 8 / TiDB Cloud (MySQL-compatible), PDO with prepared statements |
| Auth | JWT HS256 via `firebase/php-jwt`, 24-hour tokens |
| Config | `vlucas/phpdotenv` locally, real environment variables in production |
| Tests | PHPUnit 9.6 |
| Deploy | Docker image on Render, database on TiDB Cloud Serverless over TLS |

## Architecture

```
public/index.php        entry point: loads config, applies CORS, dispatches the route
app/Routes/api.php      route table
app/Routes/Router.php   path matching, auth gate, controller dispatch
app/Middleware/         CorsMiddleware, AuthMiddleware
app/Controllers/        one controller per resource
app/Models/             data access per table
app/Libraries/          Database (PDO + TLS), JwtService
app/Config/Config.php   reads settings from $_ENV, $_SERVER or getenv()
migrate.php             waits for the database and creates the schema
docker-entrypoint.sh    runs the migration, then starts the server on $PORT
```

Every route requires a `Bearer` token except `/api/register`, `/api/login` and `/api/health`. The router enforces this itself, so a new route is protected by default and no controller has to remember to check.

## Endpoints

All paths are under `/api`. Request and response bodies are JSON.

| Resource | Routes |
|---|---|
| Health | `GET /health` |
| Auth | `POST /register`, `POST /login` |
| Users | `GET` · `PUT` · `DELETE /users/{id}` (own user only) |
| Animals | `GET /animais`, `POST /animais`, `GET` · `PUT` · `DELETE /animais/{id}` |
| Habitats | `GET /habitats`, `POST /habitats`, `GET` · `PUT` · `DELETE /habitats/{id}` |
| Veterinarians | `GET /veterinarios`, `POST /veterinarios`, `GET` · `PUT` · `DELETE /veterinarios/{id}` |
| Staff | `GET /colaboradores`, `POST /colaboradores`, `GET` · `PUT` · `DELETE /colaboradores/{id}`, `PUT /colaboradores/{id}/delegar` |

Quick check against the live API:

```bash
curl https://zoologic-api.onrender.com/api/health
# {"status":"OK","message":"API is running"}
```

Without a token, protected routes answer `401 {"error":"Autenticação necessária"}`.

## Running locally

With Docker, which follows the same path as the production image:

```bash
docker network create zoologic
docker run -d --name zoologic-db --network zoologic \
  -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=zoologicdb mysql:8

cp .env.example .env    # then set DB_HOST=zoologic-db, DB_PASS=root and a JWT_SECRET
docker build -t zoologic-api .
docker run --rm --network zoologic -p 8000:8000 --env-file .env zoologic-api
```

Without Docker, with PHP 8, Composer and a MySQL server:

```bash
composer install
cp .env.example .env    # fill in the database settings and JWT_SECRET
php migrate.php
php -S localhost:8000 -t public
```

Then open http://localhost:8000/api/health. `seed_demo.sql` loads sample animals, habitats and veterinarians.

## Configuration

Every setting comes from the environment. A `.env` file is read when present and ignored when absent, so the same image boots both locally and on a managed platform.

| Variable | Default | Purpose |
|---|---|---|
| `PORT` | `8000` | Port the server binds. Platforms set it themselves. |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | — | Database connection. |
| `DB_MANAGED` | `false` | Set to `true` for a platform-provisioned database: the migration then only creates tables and never runs `CREATE DATABASE`. |
| `DB_SSL_CA` | empty | Path to a CA bundle. Setting it turns TLS on. |
| `DB_SSL_VERIFY` | `true` | Verify the server certificate. |
| `RUN_MIGRATIONS` | `true` | Run `migrate.php` on boot. |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:3000` | Exact origins allowed to send credentialed requests, comma separated. The deployed front end proxies through its own origin and does not need to be listed. |
| `JWT_SECRET` | — | Signing key for tokens. Required. |
| `DB_PERSISTENT` | `true` | Reuse the database connection across requests. |
| `PHP_CLI_SERVER_WORKERS` | `4` | Worker processes of the built-in server. |

## Tests

```bash
composer install
php vendor/bin/phpunit
```

There are 15 unit tests, covering the animal and staff controllers and models.

## Design notes

These decisions were made while taking the 2024 version to production.

- **TLS is turned on by the CA, not by a flag.** An earlier `DB_SSL=true` switch encrypted nothing. Measured against MySQL 8, `Ssl_cipher` came back empty, because the MySQL client only negotiates TLS when an SSL attribute is present. `DB_SSL_CA` is now the only mechanism, and certificate verification is on by default.
- **A failed migration fails the boot.** `migrate.php` used to catch the error and exit 0, so the server came up and answered every request with a database error. It now exits 1, and the entrypoint's `set -e` turns that into a failed deploy.
- **CORS lives in one place.** The middleware existed but was never called, while an inline block in `index.php` did the work with its own copy of the origin list. The middleware is now the only source:
  - it reads the allowlist from the environment;
  - it echoes back only exact origins, because credentialed requests rule out `*`;
  - it sends `Vary: Origin`;
  - it answers preflight requests with `204`.
- **Managed databases need a different migration.** Hosted MySQL users usually lack the grant for `CREATE DATABASE`, so with `DB_MANAGED=true` the migration connects straight to the provisioned database. The database name is validated before it goes into DDL, because identifiers cannot be bound as parameters.

## Performance

The free instance has 0.1 vCPU, so the work done per request shows directly in the response time. Measured locally with the container limited to `--cpus=0.1` and TLS to MySQL, mean of 60 requests per endpoint:

| | Before | After |
|---|---|---|
| List endpoints (`/animais`, `/habitats`, ...) | 47–60 ms | 19–23 ms |
| Three parallel calls, as the dashboard makes | 150–169 ms | 64–69 ms |

What changed:

- **Persistent database connection.** Opening a TLS connection and authenticating took about 80 ms of an 85 ms list request. The connection now survives across requests in each worker; 30 consecutive requests caused no new TLS handshake on the database. PDO pings a pooled connection before reusing it, so a connection the server dropped is replaced transparently, verified by killing every connection from the database side.
- **Four server workers.** The built-in server handled one request at a time, so the dashboard's three calls ran in sequence and a 600 ms login (bcrypt) stalled everything behind it.
- **OPcache under the CLI SAPI**, which the built-in server runs in and where OPcache is off by default.
- **No dev dependencies in the image**, with an authoritative classmap.
