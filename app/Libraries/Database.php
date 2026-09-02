<?php
namespace App\Libraries;

use App\Config\Config;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->pdo = new \PDO(
            self::dsn(),
            Config::get('DB_USER'),
            Config::get('DB_PASS', ''),
            self::pdoOptions()
        );
    }

    /**
     * Build the MySQL DSN.
     *
     * The port is explicit because managed MySQL rarely listens on 3306:
     * TiDB Cloud uses 4000 and Northflank assigns its own.
     */
    public static function dsn($includeDatabase = true)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s',
            Config::get('DB_HOST', '127.0.0.1'),
            Config::get('DB_PORT', '3306')
        );

        // The migration connects to the server before the database exists, so
        // it asks for a DSN without the dbname.
        if ($includeDatabase) {
            $dsn .= ';dbname=' . Config::get('DB_NAME', '');
        }

        return $dsn . ';charset=utf8mb4';
    }

    /**
     * PDO options, with TLS added when a CA bundle is configured.
     *
     * Managed MySQL providers only accept encrypted connections. TLS is driven
     * by DB_SSL_CA alone, because mysqlnd only negotiates an encrypted
     * connection when an SSL attribute is present: setting
     * MYSQL_ATTR_SSL_VERIFY_SERVER_CERT by itself leaves the traffic in the
     * clear, verified empirically against MySQL 8 (Ssl_cipher came back empty).
     *
     * For a provider with a certificate signed by a public CA, such as TiDB
     * Cloud, the system bundle is enough:
     *   DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt
     *
     * DB_SSL_VERIFY should stay on. Turning it off keeps the traffic encrypted
     * but stops checking who is on the other end, and is only for a provider
     * whose certificate the bundle cannot chain.
     */
    public static function pdoOptions()
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        $ca = Config::get('DB_SSL_CA');

        if ($ca !== null && $ca !== '') {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = $ca;
            $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = Config::bool('DB_SSL_VERIFY', true);
        }

        return $options;
    }

    // Prevent cloning of the instance
    public function __clone() {}

    // Prevent unserialization of the instance
    public function __wakeup() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Your existing methods
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function queryOne($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Added methods to support the User model
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    // Additional useful methods
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    // Method to check database connection
    public function isConnected()
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Get the raw PDO instance if needed
    public function getPdo()
    {
        return $this->pdo;
    }

    // Method to safely close the connection
    public function closeConnection()
    {
        $this->pdo = null;
        self::$instance = null;
    }
}