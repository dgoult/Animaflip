<?php
namespace App\Models;

use App\Database;
use PDO;


class Animation
{
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
}