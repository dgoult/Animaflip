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

    //API-ADMIN-VIEWS
    protected $view;

    public function __construct(Twig $view = null)
    {
        if ($view !== null) {
            $this->view = $view;
        } else {
            $this->view = null;
        }
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
    
    public function assignAnimationToTheme(Request $request, Response $response, array $args): Response
    {
        $themeId = $args['theme_id'];
        $animationId = $args['animation_id'];
    
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO theme_animation (theme_id, animation_id) VALUES (:theme_id, :animation_id)');
        $stmt->execute(['theme_id' => $themeId, 'animation_id' => $animationId]);
    
        return $response->withHeader('Location', '/admin/theme/' . $themeId . '/edit')->withStatus(302);
    }
    
    public function unassignAnimationFromTheme(Request $request, Response $response, array $args): Response
    {
        $themeId = $args['theme_id'];
        $animationId = $args['animation_id'];
    
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM theme_animation WHERE theme_id = :theme_id AND animation_id = :animation_id');
        $stmt->execute(['theme_id' => $themeId, 'animation_id' => $animationId]);
    
        return $response->withHeader('Location', '/admin/theme/' . $themeId . '/edit')->withStatus(302);
    }

    public function listThemes(Request $request, Response $response, array $args): Response
    {
        $themes = Theme::all();
        return $this->view->render($response, 'admin/themes/list.html.twig', ['themes' => $themes]);
    }

    public function newThemeForm(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'admin/themes/new.html.twig');
    }

    public function createTheme(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        Theme::create(['libelle' => $data['libelle']]);
        return $response->withHeader('Location', '/admin/themes')->withStatus(302);
    }

    public function editThemeForm(Request $request, Response $response, array $args): Response
    {
        $theme = Theme::find($args['id']);
        return $this->view->render($response, 'admin/themes/edit.html.twig', ['theme' => $theme]);
    }

    public function updateTheme(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        Theme::find($args['id'])->update(['libelle' => $data['libelle']]);
        return $response->withHeader('Location', '/admin/themes')->withStatus(302);
    }

    public function deleteTheme(Request $request, Response $response, array $args): Response
    {
        Theme::find($args['id'])->delete();
        return $response->withHeader('Location', '/admin/themes')->withStatus(302);
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