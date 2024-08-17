<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Theme;
use App\Models\Animation;

class ThemeController
{
    public function getThemes(Request $request, Response $response, $args)
    {
        $themes = Theme::all();
        
        // Vérifier si la récupération des thèmes a échoué
        if ($themes === false) {
            $response->getBody()->write(json_encode(['error' => 'Failed to retrieve themes']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        // Ajouter les animations pour chaque thème
        foreach ($themes as &$theme) {
            $animations = Animation::allByTheme($theme['id']);
            if ($animations === false) {
                $response->getBody()->write(json_encode(['error' => 'Failed to retrieve animations for theme id ' . $theme['id']]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
            // Convertir les chaînes en UTF-8
            foreach ($animations as &$animation) {
                $animation = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
                }, $animation);
            }
            $theme['animations'] = $animations;
        }

        // Convertir les chaînes en UTF-8 pour les thèmes
        foreach ($themes as &$theme) {
            $theme = array_map(function($value) {
                return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'auto') : $value;
            }, $theme);
        }

        file_put_contents('debugGetThemes.txt', print_r($themes, TRUE));

        // Encoder les résultats en JSON
        $result = json_encode($themes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // Vérifier si l'encodage JSON a échoué
        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'JSON encoding error']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
// namespace App\Controllers;

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use App\Models\Theme;
// use App\Models\Animation;

// class ThemeController
// {
//     public function getThemes(Request $request, Response $response, $args)
//     {
//         $themes = Theme::all();
//         foreach ($themes as &$theme) {
//             $theme['animations'] = Animation::allByTheme($theme['id']);
//         }

//         file_put_contents('debugGetThemes.txt', print_r($themes, TRUE));
//         $response->getBody()->write(json_encode($themes));
//         return $response->withHeader('Content-Type', 'application/json');
//     }
// }