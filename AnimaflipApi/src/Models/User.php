<?php
namespace App\Models;
use App\Database;

use PDO;

class User
{
    public static function authenticate($username, $password)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public static function create($username, $password, $role = 'user')
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
        return $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, username, role FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}