<?php
namespace App\Models;

use App\Database;
use PDO;
use PDOException;

class Theme
{
    // Vérifier si le libellé existe déjà
    public static function libelleExists($libelle) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM themes WHERE libelle = :libelle");
        $stmt->execute(['libelle' => $libelle]);
        return $stmt->fetchColumn() > 0;
    }

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
        if (self::libelleExists($libelle)) {
            throw new \Exception("Le libellé du thème existe déjà.");
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('INSERT INTO themes (libelle) VALUES (:libelle)');
            $stmt->execute(['libelle' => $libelle]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la création du thème: ' . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour un thème
    public static function updateTheme($id, $libelle)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('UPDATE themes SET libelle = :libelle WHERE id = :id');
            return $stmt->execute(['libelle' => $libelle, 'id' => $id]);
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la mise à jour de l\'animation: ' . $e->getMessage());
            return false;
        }
    }

    // Supprimer un thème
    public static function deleteTheme($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM themes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
    
    // Assigner une animation à un thème
    public static function assignAnimationToTheme($animationId, $themeId)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('INSERT INTO theme_animation (theme_id, animation_id) VALUES (:theme_id, :animation_id)');
            $stmt->execute([
                'theme_id' => $themeId,
                'animation_id' => $animationId
            ]);
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la mise à jour de l\'animation: ' . $e->getMessage());
            return false;
        }
    }

    // Désassigner tous les thèmes d'une animation
    public static function unassignAllThemesFromAnimation($animationId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM theme_animation WHERE animation_id = :animation_id');
        $stmt->execute(['animation_id' => $animationId]);
    }

    // Méthode pour désaffecter toutes les animations d'un thème
    public static function unassignAllAnimationsFromTheme($themeId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM theme_animation WHERE theme_id = :theme_id');
        $stmt->execute(['theme_id' => $themeId]);
    }
}