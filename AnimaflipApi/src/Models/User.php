<?php
namespace App\Models;
use App\Database;

use PDO;
use PDOException;
use \Exception;

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

    // Créer un utilisateur
    public static function create($username, $password, $role)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $result = $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);

            // Vérifier si l'exécution s'est bien passée, sinon loguer
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log('Erreur lors de l\'insertion : ' . print_r($errorInfo, true));
            }

            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // Log de l'exception pour vérifier si elle est bien capturée
            error_log('Erreur PDOException capturée : ' . $e->getMessage());
            // Gestion spécifique de l'exception de contrainte d'unicité (duplicate entry)
            if ($e->getCode() == 23000) {
                throw new \Exception('Un utilisateur avec ce nom existe déjà.');
            } else {
                throw new \Exception('Erreur lors de la création de l\'utilisateur: ') . $e->getMessage();
            }
        }
    }

    // Obtenir tous les utilisateurs
    public static function getAllUsers()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir un utilisateur par ID
    public static function getById($userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
            
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateUser($userId, $username, $password = null, $role = null)
    {
        try {
            $pdo = Database::getConnection();
                
            $query = 'UPDATE users SET username = :username';
            $params = ['username' => $username, 'id' => $userId];

            if (!empty($password)) {
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
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la modification de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
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

    public static function deleteUser($userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $userId]);
    }
}