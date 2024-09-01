<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use App\Models\Theme;
use App\Models\Animation;

class ThemeController
{
    public function getThemes(Request $request, Response $response, $args)
    {
        $themes = Theme::all();
        
        // Vérifier si la récupération des thèmes a échoué
        if ($themes === false) {
            $response->getBody()->write(json_encode(['error' => 'Echec de la recuperation des themes']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        // Ajouter les animations pour chaque thème
        foreach ($themes as &$theme) {
            $animations = Animation::allByTheme($theme['id']);
            if ($animations === false) {
                $response->getBody()->write(json_encode(['error' => 'Echec de la recuperation des animations pour le theme ID ' . $theme['id']]));
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
            $response->getBody()->write(json_encode(['error' => 'Erreur d\'encodage JSON']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getThemesByUserId(Request $request, Response $response, array $args): Response
    {
        $userId = $args['user_id'];
        $themes = User::getThemesByUserId($userId);

        file_put_contents('debugGetThemesById.txt', print_r($themes, TRUE));

        if (empty($themes)) {
            $response->getBody()->write(json_encode(['error' => 'Aucun theme n\'est assigne a cet utilisateur']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        // Ajouter les animations pour chaque thème
        foreach ($themes as &$theme) {
            $animations = Animation::allByTheme($theme['id']);
            if ($animations === false) {
                $response->getBody()->write(json_encode(['error' => 'Echec de la recuperation des animations pour le theme ID ' . $theme['id']]));
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
            $response->getBody()->write(json_encode(['error' => 'Erreur d\'encodage JSON']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function assignThemeToUser(Request $request, Response $response, array $args): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $userId = $data['user_id'];
        $themeId = $data['theme_id'];

        $result = User::assignThemeToUser($userId, $themeId);

        if ($result) {
            $response->getBody()->write(json_encode(['message' => 'Theme assigne a l\'utilisateur avec succes.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Erreur lors de l\'affectation du theme a l\'utilisateur.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function unassignThemeFromUser(Request $request, Response $response, array $args): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $userId = $data['user_id'];
        $themeId = $data['theme_id'];

        $result = User::unassignThemeFromUser($userId, $themeId);

        if ($result) {
            $response->getBody()->write(json_encode(['message' => 'Thème désassigné de l\'utilisateur avec succès.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Erreur lors de la désassignation du thème pour l\'utilisateur.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
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