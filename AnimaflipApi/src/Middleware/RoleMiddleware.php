<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class RoleMiddleware
{
    private $requiredRole;

    public function __construct($requiredRole)
    {
        $this->requiredRole = $requiredRole;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $user = $request->getAttribute('user');

        file_put_contents('debugRoleMiddleware.txt', print_r($user, TRUE));

        if ($user && $user->role === $this->requiredRole) {
            return $handler->handle($request);
        }

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Forbidden']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}