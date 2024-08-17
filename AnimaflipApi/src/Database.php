<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    public static function getConnection()
    {
        $host = $_ENV['DB_HOST'] ?? null;
        $dbname = $_ENV['DB_NAME'] ?? null;
        $username = $_ENV['DB_USER'] ?? null;
        $password = $_ENV['DB_PASS'] ?? null;

        if (!$host || !$dbname || !$username) { // || !$password
            throw new PDOException('Database configuration not set in .env file');
        }

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}