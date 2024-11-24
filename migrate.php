<?php
require_once __DIR__ . '/vendor/autoload.php';

// Carrega as variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Função para aguardar o banco estar disponível
function waitForDatabase($host, $user, $pass, $port = 3306, $retries = 10, $delay = 5) {
    $dsn = "mysql:host={$host};port={$port}";
    while ($retries > 0) {
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo = null;
            echo "Banco de dados está disponível!\n";
            return;
        } catch (PDOException $e) {
            echo "Aguardando banco de dados... ({$retries} tentativas restantes)\n";
            sleep($delay);
            $retries--;
        }
    }
    throw new Exception("Banco de dados indisponível após várias tentativas.");
}

try {
    // Aguarda o banco de dados ficar disponível
    waitForDatabase($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_PORT']);

    // Conecta ao MySQL sem selecionar banco
    $pdo = new PDO(
        "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cria o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $_ENV['DB_NAME'] . 
               " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados criado ou já existente!\n";

    // Seleciona o banco
    $pdo->exec("USE " . $_ENV['DB_NAME']);

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
    echo "Erro: " . $e->getMessage() . "\n";
}
