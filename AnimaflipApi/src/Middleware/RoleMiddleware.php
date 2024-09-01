<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use \Firebase\JWT\JWT;
use App\Models\User;

class RoleMiddleware
{
    private $requiredRole;

    public function __construct($requiredRole)
    {
        $this->requiredRole = $requiredRole;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $authHeader = $request->getHeader('Authorization');
        if ($authHeader) {
            $token = str_replace('Bearer ', '', $authHeader[0]);

            try {
                $secret = $_ENV['JWT_SECRET'];
                $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));

                // Récupérer l'utilisateur par ID
                $user = User::getById($decoded->id);

                file_put_contents('debugRoleMiddleware.txt', print_r($user, TRUE));

                if ($user && $user['role'] === $this->requiredRole) {
                    return $handler->handle($request);
                }

                // Si le rôle ne correspond pas
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Forbidden, votre rôle ne vous donne pas accès à cette route']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);

            } catch (\Exception $e) {
                // Erreur lors du décodage du token ou autre problème
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } else {
            // Si le header Authorization est manquant
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Authorization header missing']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}