<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use \Firebase\JWT\JWT;
use App\Models\Blacklist;
use App\Models\User;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $authHeader = $request->getHeader('Authorization');
        if ($authHeader) {
            $token = str_replace('Bearer ', '', $authHeader[0]);

            try {
                $secret = $_ENV['JWT_SECRET'];
                $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));

                // Ajouter l'utilisateur décodé en tant qu'attribut de la requête 
                $request = $request->withAttribute('tokenInfo', $decoded);

                // Récupérer les détails de l'utilisateur à partir de la base de données
                $user = User::getById($decoded->id);
                if (!$user) {
                    $response = new \Slim\Psr7\Response();
                    $response->getBody()->write(json_encode(['error' => 'User not found']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
                }

                // Ajouter les détails de l'utilisateur en tant qu'attribut de la requête
                $request = $request->withAttribute('user', $user);

                file_put_contents('debugAuthMiddleware.txt', print_r($user, TRUE));

                return $handler->handle($request);

            } catch (\Firebase\JWT\ExpiredException $e) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Token expired']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            } catch (\Exception $e) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } else {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
