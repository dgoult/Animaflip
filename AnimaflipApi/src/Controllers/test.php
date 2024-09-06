<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Controllers\ThemeController;
use App\Models\User;
use App\Models\Theme;
use \Firebase\JWT\JWT;

class AdminController
{
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    // Afficher le formulaire de login
    public function loginForm(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'login.html.twig');
    }

    // Afficher le formulaire de création d'utilisateur
    public function createUserForm(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'user_form.html.twig');
    }

    // Méthode pour vérifier si l'utilisateur est admin à partir du token
    public function isAdmin($token)
    {
        try {
            $secret = $_ENV['JWT_SECRET'];
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));

            // Vérifiez si l'utilisateur est un administrateur
            if ($decoded->role === 'admin') {
                return true;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return false; // Si le token est invalide ou expire
        }
    }

    public function createUser(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];

        if (!$this->isAdmin($token)) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(403)
                            ->write(json_encode(['error' => 'Accès refusé']));
        }

        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'];

        $user = User::create($username, $password, $role);
        if ($user) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode(['message' => 'Utilisateur créé avec succès']));
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['error' => 'Erreur lors de la création de l\'utilisateur']));
        }
    }

    public function updateUser(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
    
        // Vérifier si l'utilisateur est admin à partir du token
        if (!$this->isAdmin($token)) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(403)
                            ->write(json_encode(['error' => 'Accès refusé']));
        }
    
        $userId = $args['id'];
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? null;
    
        if ($username === null) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400)
                            ->write(json_encode(['error' => 'Le nom d\'utilisateur est requis']));
        }
    
        // Mise à jour de l'utilisateur
        $result = User::updateUser($userId, $username, $password, $role);
        if ($result) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode(['message' => 'Utilisateur mis à jour avec succès']));
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']));
        }
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];

        if (!$this->isAdmin($token)) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(403)
                            ->write(json_encode(['error' => 'Accès refusé']));
        }

        $userId = $args['id'];
        $result = User::deleteUser($userId);

        if ($result) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode(['message' => 'Utilisateur supprimé avec succès']));
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['error' => 'Erreur lors de la suppression de l\'utilisateur']));
        }
    }

    public function logout(Request $request, Response $response, array $args): Response
    {
        // Démarrer la session
        session_start();

        // Détruire la session pour déconnecter l'utilisateur
        session_unset();
        session_destroy();

        // Rediriger vers la page de login
        return $response
            ->withHeader('Location', '/admin/login')
            ->withStatus(302);
    }

    // // Mettre à jour un utilisateur
    // public function updateUser(Request $request, Response $response, array $args): Response
    // {
    //     $userId = $args['id'];
    //     $data = $request->getParsedBody();
    //     $username = $data['username'] ?? null;
    //     $password = $data['password'] ?? null;
    //     $role = $data['role'] ?? 'user';

    //     if ($username) {
    //         $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
    //         User::updateUser($userId, $username, $hashedPassword, $role);
    //         return $response->withHeader('Location', '/admin/users')->withStatus(302);
    //     }

    //     return $this->view->render($response, 'update_user.html.twig', [
    //         'error' => 'Le nom d\'utilisateur est requis.',
    //         'user' => User::getById($userId)
    //     ]);
    // }

    // // Supprimer un utilisateur
    // public function deleteUser(Request $request, Response $response, array $args): Response
    // {
    //     $userId = $args['id'];
    //     User::deleteUser($userId);
    //     return $response->withHeader('Location', '/admin/users')->withStatus(302);
    // }

    
    // // Afficher le tableau de bord
    // public function dashboard(Request $request, Response $response, array $args): Response
    // {
    //     session_start();
    //     if (!isset($_SESSION['token'])) {
    //         return $response
    //             ->withHeader('Location', '/admin/login')
    //             ->withStatus(302);
    //     }

    //     // Récupérer les thèmes
    //     $themes = Theme::all();

    //     // Récupérer les utilisateurs
    //     $users = User::getAllUsers();

    //     return $this->view->render($response, 'dashboard.html.twig', [
    //         'user' => $_SESSION['user'],
    //         'themes' => $themes,
    //         'users' => $users
    //     ]);
    // }
}