<?php

/**
 * Database Connection Class
 */

class Database
{
    private $connection;

    public function __construct()
    {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());

            if (DEBUG_MODE) {
                die('Database connection error: ' . $e->getMessage());
            } else {
                die('Database connection error. Please contact support.');
            }
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
