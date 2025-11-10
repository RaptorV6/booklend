<?php
namespace app;

use PDO;
use PDOException;

class Database {
    private ?PDO $pdo = null;
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;

    public function __construct(string $host, string $dbname, string $username, string $password) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }

    private function connect(): PDO {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                die("Database connection failed");
            }
        }
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $sql, array $params = []): int {
        $this->query($sql, $params);
        return $this->lastInsertId();
    }

    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(): int {
        return (int) $this->connect()->lastInsertId();
    }
}
