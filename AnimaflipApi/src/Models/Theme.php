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
}