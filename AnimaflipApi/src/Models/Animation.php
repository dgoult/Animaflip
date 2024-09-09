<?php
namespace App\Models;

use App\Database;
use PDO;
use PDOException;

class Animation
{
    // Vérifier si le libellé existe déjà
    public static function libelleExists($libelle) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM animations WHERE libelle = :libelle");
        $stmt->execute(['libelle' => $libelle]);
        return $stmt->fetchColumn() > 0;
    }
    
    public static function all()
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('SELECT * FROM animations');
            $animations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir les chaînes en UTF-8
            foreach ($animations as &$animation) {
                $animation = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
                }, $animation);
            }

            return $animations;
        } catch (PDOException $e) {
            error_log('Failed to retrieve animations: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function allWithThemes()
    {
        try {
            $pdo = Database::getConnection();
            // Requête pour récupérer les animations avec leurs thèmes (id et libelle) associés
            $stmt = $pdo->query('
                SELECT animations.id, animations.libelle, animations.video_url, 
                    GROUP_CONCAT(CONCAT(themes.id, "-", themes.libelle) SEPARATOR ", ") AS themes 
                FROM animations 
                LEFT JOIN theme_animation ON animations.id = theme_animation.animation_id 
                LEFT JOIN themes ON theme_animation.theme_id = themes.id 
                GROUP BY animations.id
            ');
            $animations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir les chaînes en UTF-8
            foreach ($animations as &$animation) {
                // Convertir les données de l'animation
                $animation = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
                }, $animation);

                // Si des thèmes existent, les séparer en tableaux d'id et libelle
                if (!empty($animation['themes'])) {
                    $themesArray = explode(', ', $animation['themes']);
                    $animation['themes'] = array_map(function($theme) {
                        list($id, $libelle) = explode('-', $theme);
                        return [
                            'id' => (int) $id,
                            'libelle' => $libelle
                        ];
                    }, $themesArray);
                }
            }

            return $animations;
        } catch (PDOException $e) {
            error_log('Failed to retrieve animations: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function allIdsByTheme($themeId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT animation_id FROM theme_animation WHERE theme_id = :theme_id");
        $stmt->execute(['theme_id' => $themeId]);
    
        return $stmt->fetchAll(PDO::FETCH_COLUMN);  // Retourne un tableau d'IDs d'animations associées
    }

    // Récupérer toutes les animations par thème
    public static function allByTheme($themeId)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT animations.* 
                FROM animations 
                JOIN theme_animation ON animations.id = theme_animation.animation_id 
                WHERE theme_animation.theme_id = :theme_id
            ');
            $stmt->execute(['theme_id' => $themeId]);
            $animations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir les chaînes en UTF-8
            foreach ($animations as &$animation) {
                $animation = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
                }, $animation);
            }

            return $animations;
        } catch (PDOException $e) {
            error_log('Failed to retrieve animations for theme id ' . $themeId . ': ' . $e->getMessage());
            return false;
        }
    }
    
    public static function getThemesByAnimationId($animationId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT themes.* FROM themes 
            JOIN theme_animation ON themes.id = theme_animation.theme_id 
            WHERE theme_animation.animation_id = :animation_id
        ');
        $stmt->execute(['animation_id' => $animationId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ); // Retourne les thèmes sous forme d'objets
    }

    // Récupérer une animation par son ID
    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM animations WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $animation = $stmt->fetch(PDO::FETCH_OBJ); // Assure-toi de récupérer un objet

        if ($animation) {
            $animation->themes = self::getThemesByAnimationId($animation->id); // Associer les thèmes
        }

        return $animation;
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'theme_animation', 'animation_id', 'theme_id');
    }

    // Créer une nouvelle animation
    public static function create($libelle, $videoUrl)
    {
        if (self::libelleExists($libelle)) {
            throw new \Exception("Le libellé de l'animation existe déjà.");
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('INSERT INTO animations (libelle, video_url) VALUES (:libelle, :video_url)');
            $stmt->execute([
                'libelle' => $libelle,
                'video_url' => $videoUrl
            ]);

            // Retourner l'ID de l'animation nouvellement créée
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la création de l\'animation: ' . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour une animation
    public static function updateAnimation($id, $libelle, $videoUrl)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('UPDATE animations SET libelle = :libelle, video_url = :video_url WHERE id = :id');
            $stmt->execute([
                'libelle' => $libelle,
                'video_url' => $videoUrl,
                'id' => $id
            ]);

            return true;
        } catch (PDOException $e) {
            // En cas d'erreur, enregistrer un message d'erreur et renvoyer false
            error_log('Erreur lors de la mise à jour de l\'animation: ' . $e->getMessage());
            return false;
        }
    }

    // Supprimer une animation
    public static function deleteAnimation($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM animations WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}