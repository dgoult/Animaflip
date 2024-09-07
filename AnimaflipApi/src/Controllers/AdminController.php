<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\User;
use App\Models\Theme;
use App\Models\Animation;
use \Firebase\JWT\JWT;

class AdminController
{
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
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

    // Afficher le formulaire de login
    public function loginForm(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'login.html.twig');
    }

    // Gérer la connexion
    public function login(Request $request, Response $response, array $args): Response
    {
        // Démarrer la session
        session_start();

        // Récupérer les données du formulaire POST
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        // Vérifiez si les champs sont remplis
        if ($username === null || $password === null) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Le nom d\'utilisateur et le mot de passe sont requis.'
            ]);
        }

        // Authentification de l'utilisateur
        $user = User::authenticate($username, $password);
        if ($user) {
            $secret = $_ENV['JWT_SECRET'];

            $now = time();
            $exp = $now + 3600; // 1 heure

            $payload = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'iat' => $now,
                'exp' => $exp
            ];

            $token = JWT::encode($payload, $secret, 'HS256');

            // Stocker les informations utilisateur et token dans la session
            $_SESSION['token'] = $token;
            $_SESSION['user'] = $user;

            // Rediriger vers le tableau de bord après la connexion réussie
            return $response->withHeader('Location', '/admin/dashboard/' . $token)->withStatus(302);
        } else {
            // Nom d'utilisateur ou mot de passe incorrect
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Nom d\'utilisateur ou mot de passe incorrect'
            ]);
        }
    }

    // Afficher le tableau de bord
    public function dashboard(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
    
        // Vérifier si l'utilisateur est admin à partir du token
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        // Démarrer la session
        session_start();

        // Vérifier si le token existe dans la session
        if (!isset($_SESSION['token'])) {
            return $response
                ->withHeader('Location', '/admin/login')
                ->withStatus(302);
        }

        // Récupérer les utilisateurs et les thèmes depuis la base de données
        $users = User::getAllUsers();
        $themes = Theme::all();

        // Afficher le tableau de bord avec les informations utilisateur
        return $this->view->render($response, 'dashboard.html.twig', [
            'token' => $_SESSION['token'], // Token JWT stocké dans la session
            'user' => $_SESSION['user'],  // Informations utilisateur stockées dans la session
            'themes' => $themes,
            'users' => $users
        ]);
    }

    // Gérer la déconnexion
    public function logout(Request $request, Response $response, array $args): Response
    {
        session_start();
        session_unset();
        session_destroy();

        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    }

    // Méthode pour affecter un thème à un utilisateur
    public function assignTheme(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        User::assignThemeToUser($args['id'], $args['theme_id']);

        // Retourner à la page de modification de l'utilisateur
        return $response->withHeader('Location', '/admin/user/' . $args['id'] . '/edit/' . $args['token'])->withStatus(302);
    }

    // Méthode pour désaffecter un thème à un utilisateur
    public function unassignTheme(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        User::unassignThemeFromUser($args['id'], $args['theme_id']);

        // Retourner à la page de modification de l'utilisateur
        return $response->withHeader('Location', '/admin/user/' . $args['id'] . '/edit/' . $args['token'])->withStatus(302);
    }

    // Formulaire de création d'utilisateur
    public function createUserForm(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        return $this->view->render($response, 'user_form.html.twig', ['token' => $args['token']]);
    }

    // Création d'utilisateur
    public function createUser(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'];

        $user = User::create($username, $password, $role);

        if ($user) {
            return $response->withHeader('Location', '/admin/dashboard/' . $token)->withStatus(302);
        } else {
            return $response->withHeader('Location', '/admin/user/create')->withStatus(500);
        }
    }

    // Formulaire de mise à jour d'utilisateur
    public function updateUserForm(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        // Récupérer l'utilisateur et tous les thèmes
        $user = User::getById($args['id']);
        $themes = Theme::all();

        // Récupérer les thèmes déjà affectés à cet utilisateur
        $assignedThemes = User::getThemesByUserId($args['id']);

        file_put_contents('updateUserForm.txt', print_r($assignedThemes, TRUE));
        // Marquer les thèmes comme affectés ou non
        foreach ($themes as &$theme) {
            $theme['assigned'] = in_array($theme['id'], array_column($assignedThemes, 'id'));
        }

        return $this->view->render($response, 'user_form.html.twig', [
            'token' => $args['token'], 
            'user' => $user,
            'themes' => $themes
        ]);
    }

    // Mise à jour d'utilisateur
    public function updateUser(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $data = $request->getParsedBody();
        $userId = $args['id'] ?? null;
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? null;

        $result = User::updateUser($userId, $username, $password, $role);

        if ($result) {
            return $response->withHeader('Location', '/admin/dashboard/' . $args['token'])->withStatus(302);
        } else {
            $currentUrl = (string)$request->getUri();
        file_put_contents('passwordddd.txt', print_r($currentUrl, TRUE));
            return $response->withHeader('Location', $currentUrl)->withStatus(500); //, '/admin/user/' . $args['id'] . '/edit/' . $args['token']
        }
    }

    // Suppression d'utilisateur
    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $result = User::deleteUser($args['id']);

        if ($result) {
            return $response->withHeader('Location', "/admin/dashboard/{$args['token']}")->withStatus(302);
        } else {
            return $response->withHeader('Location', "/admin/dashboard/{$args['token']}")->withStatus(500);
        }
    }

    // Formulaire de création d'un theme
    public function createThemeForm(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        return $this->view->render($response, 'theme_form.html.twig', ['token' => $args['token']]);
    }

    // Création du thème
    public function createTheme(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $data = $request->getParsedBody();
        $themeLibelle = $data['themeLibelle'] ?? null;

        $theme = Theme::create($themeLibelle);

        if ($theme) {
            return $response->withHeader('Location', '/admin/dashboard/' . $token)->withStatus(302);
        } else {
            return $response->withHeader('Location', '/admin/theme/create')->withStatus(500);
        }
    }

    // Formulaire de mise à jour d'utilisateur
    public function updateThemeForm(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        // Récupérer le thème
        $theme = Theme::getById($args['id']);

        // Récupérer les animations déjà du thème
        $assignedAnimations = Animation::allByTheme($args['id']);

        return $this->view->render($response, 'theme_form.html.twig', [
            'token' => $args['token'], 
            'theme' => $theme,
            'assignedAnimations' => $assignedAnimations
        ]);
    }

    // Mise à jour du thème
    public function updateTheme(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $data = $request->getParsedBody();
        $themeId = $args['id'] ?? null;
        $themeLibelle = $data['themeLibelle'] ?? null;

        $result = Theme::updateTheme($themeId, $themeLibelle);

        if ($result) {
            return $response->withHeader('Location', '/admin/dashboard/' . $args['token'])->withStatus(302);
        } else {
            $currentUrl = (string)$request->getUri();
            return $response->withHeader('Location', $currentUrl)->withStatus(500);
        }
    }

    // Suppression du thème
    public function deleteTheme(Request $request, Response $response, array $args): Response
    {
        // Vérifier si l'utilisateur est admin à partir du token
        $token = $args['token'];
        if (!$this->isAdmin($token)) {
            return $this->view->render($response, 'login.html.twig', [
                'error' => 'Accès non authorisé !'
            ]);
        }

        $result = Theme::deleteTheme($args['id']);

        if ($result) {
            return $response->withHeader('Location', "/admin/dashboard/{$args['token']}")->withStatus(302);
        } else {
            return $response->withHeader('Location', "/admin/dashboard/{$args['token']}")->withStatus(500);
        }
    }
}