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

    public static function getAllUsers()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT id, username, role FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getThemesByUserId($userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT themes.* 
            FROM themes 
            INNER JOIN user_themes ON themes.id = user_themes.theme_id 
            WHERE user_themes.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function assignThemeToUser($userId, $themeId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO user_themes (user_id, theme_id) VALUES (:user_id, :theme_id)');
        return $stmt->execute(['user_id' => $userId, 'theme_id' => $themeId]);
    }
    
    public static function unassignThemeFromUser($userId, $themeId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM user_themes WHERE user_id = :user_id AND theme_id = :theme_id');
        return $stmt->execute(['user_id' => $userId, 'theme_id' => $themeId]);
    }

    public static function updateUser($userId, $username, $password = null, $role = null)
    {
        $pdo = Database::getConnection();
        
        $query = 'UPDATE users SET username = :username';
        $params = ['username' => $username, 'id' => $userId];

        if ($password !== null) {
            $query .= ', password = :password';
            $params['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($role !== null) {
            $query .= ', role = :role';
            $params['role'] = $role;
        }

        $query .= ' WHERE id = :id';
        
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    }

    public static function deleteUser($userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $userId]);
    }
}