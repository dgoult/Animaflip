<?php
namespace App\Models;

use App\Database;
use PDO;

class Theme
{
    public static function all()
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('SELECT * FROM themes');
            $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir les chaînes en UTF-8
            foreach ($themes as &$theme) {
                $theme = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
                }, $theme);
            }

            return $themes;
        } catch (PDOException $e) {
            error_log('Failed to retrieve themes: ' . $e->getMessage());
            return false;
        }
    }

    // Récupérer un thème par son ID
    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM themes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouveau thème
    public static function create($libelle)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO themes (libelle) VALUES (:libelle)');
        return $stmt->execute(['libelle' => $libelle]);
    }

    // Mettre à jour un thème
    public static function updateTheme($id, $libelle)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE themes SET libelle = :libelle WHERE id = :id');
        return $stmt->execute(['libelle' => $libelle, 'id' => $id]);
    }

    // Supprimer un thème
    public static function deleteTheme($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM themes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }    
    
    // Assigner une animation à un thème
    public static function assignAnimationToTheme($themeId, $animationId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO theme_animation (theme_id, animation_id) VALUES (:theme_id, :animation_id)');
        $stmt->execute(['theme_id' => $themeId, 'animation_id' => $animationId]);
    }

    // Désassigner tous les thèmes d'une animation
    public static function unassignAllThemesFromAnimation($animationId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM theme_animation WHERE animation_id = :animation_id');
        $stmt->execute(['animation_id' => $animationId]);
    }
}