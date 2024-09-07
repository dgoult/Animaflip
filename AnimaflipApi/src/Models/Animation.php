<?php
namespace App\Models;

use App\Database;
use PDO;


class Animation
{
    // Récupérer toutes les animations par thème
    public static function allByTheme($themeId)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM animations WHERE theme_id = :theme_id');
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
    
    // Créer une nouvelle animation
    public static function create($themeId, $libelle, $videoUrl)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO animations (theme_id, libelle, video_url) VALUES (:theme_id, :libelle, :video_url)');
        return $stmt->execute([
            'theme_id' => $themeId,
            'libelle' => $libelle,
            'video_url' => $videoUrl
        ]);
    }

    // Mettre à jour une animation
    public static function updateAnimation($id, $libelle, $videoUrl)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE animations SET libelle = :libelle, video_url = :video_url WHERE id = :id');
        return $stmt->execute([
            'libelle' => $libelle,
            'video_url' => $videoUrl,
            'id' => $id
        ]);
    }

    // Supprimer une animation
    public static function deleteAnimation($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM animations WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}