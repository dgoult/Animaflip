<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;
use App\Models\User;

class AuthController
{
    public function login(Request $request, Response $response, $args)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        if (is_null($data)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (empty($data['username']) || empty($data['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Username and password are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $username = $data['username'];
        $password = $data['password'];

        // Vérifiez les informations d'identification de l'utilisateur
        $user = User::authenticate($username, $password);

        if ($user) {
            $secret = $_ENV['JWT_SECRET'];

            if (is_null($secret)) {
                $response->getBody()->write(json_encode(['error' => 'JWT configuration not set in .env file']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $now = time();
            $exp = $now + 3600; // 1 heure

            $payload = [
                'id' => $user['id'],
                'username' => $user['username'],
                'iat' => $now,
                'exp' => $exp
            ];

            $token = JWT::encode($payload, $secret, 'HS256');

            $response->getBody()->write(json_encode(['token' => $token]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            return $response->withStatus(401);
        }
    }

    public function register(Request $request, Response $response, $args)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        if (is_null($data)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (empty($data['username']) || empty($data['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Username and password are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $username = $data['username'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'];

        $user = User::create($username, $password, $role);

        if ($user) {
            $response->getBody()->write(json_encode(['message' => 'User created successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } else {
            $response->getBody()->write(json_encode(['error' => 'User creation failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}