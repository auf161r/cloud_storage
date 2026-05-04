<?php

namespace Core;

class Db
{
    private static ?self $instance = null;
    private \PDO $pdo;

    /**
     * Приватный конструктор (Singleton)
     */
    private function __construct()
    {
        try {
            $config = parse_ini_file(__DIR__ . '/../.env');
            $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset={$config['DB_CHARSET']}";
            $this->pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD']);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Получить единственный экземпляр класса Db
     * 
     * @return self
     */
    static public function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Получить объект подключения PDO
     * 
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }
}
