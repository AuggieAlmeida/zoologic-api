<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Config;
use App\Libraries\Database;

// The .env file is optional: managed platforms inject real environment
// variables instead of shipping a file.
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

/**
 * Wait for the database to accept connections.
 *
 * Reuses the application DSN and PDO options so the wait exercises the same
 * TLS settings the API will use, instead of a second, weaker connection.
 */
function waitForDatabase($dsn, $user, $pass, array $options, $retries = 10, $delay = 5) {
    while ($retries > 0) {
        try {
            new PDO($dsn, $user, $pass, $options);
            echo "Banco de dados está disponível!\n";
            return;
        } catch (PDOException $e) {
            $retries--;
            if ($retries === 0) {
                throw new Exception(
                    'Banco de dados indisponível após várias tentativas: ' . $e->getMessage()
                );
            }
            echo "Aguardando banco de dados... ({$retries} tentativas restantes)\n";
            sleep($delay);
        }
    }
}

try {
    $user = Config::get('DB_USER');
    $pass = Config::get('DB_PASS', '');
    $options = Database::pdoOptions();

    // A managed MySQL hands over a database that already exists and a user
    // without the CREATE DATABASE grant, so the migration connects straight to
    // it and only creates tables.
    $managed = Config::bool('DB_MANAGED');
    $dsn = Database::dsn($managed);

    waitForDatabase($dsn, $user, $pass, $options);

    $pdo = new PDO($dsn, $user, $pass, $options);

    if ($managed) {
        echo "DB_MANAGED ativo: usando o banco já provisionado.\n";
    } else {
        $name = Config::get('DB_NAME', '');

        // The database name cannot be a bound parameter, so it is validated
        // before being interpolated into DDL.
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $name)) {
            throw new Exception('DB_NAME inválido ou ausente: ' . var_export($name, true));
        }

        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$name}`" .
            " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        echo "Banco de dados criado ou já existente!\n";

        $pdo->exec("USE `{$name}`");
    }

    // Cria as tabelas necessárias
    $sql = "
    CREATE TABLE IF NOT EXISTS colaboradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        funcao VARCHAR(100) NOT NULL,
        salario DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS animais (
        id_animal INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        tipo VARCHAR(100) NOT NULL,
        especie VARCHAR(100) NOT NULL,
        setor VARCHAR(100) NOT NULL,
        habitat VARCHAR(100) NOT NULL,
        idade INT NOT NULL,
        peso DECIMAL(10,2) NOT NULL,
        alimentacao VARCHAR(255) NOT NULL,
        status VARCHAR(50) NOT NULL,
        sexo CHAR(1) NOT NULL,
        observacoes TEXT,
        foto VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);

    echo "Tabelas criadas com sucesso!\n";
} catch (Exception $e) {
    // The entrypoint runs this script before starting the server, so a failed
    // migration has to fail the process instead of printing and returning 0.
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
