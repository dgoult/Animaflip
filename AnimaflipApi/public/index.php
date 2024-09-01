<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/login', \App\Controllers\AuthController::class . ':login');
    
    $group->post('/register', \App\Controllers\AuthController::class . ':register')
     ->add(new \App\Middleware\RoleMiddleware('admin'))
     ->add(new \App\Middleware\AuthMiddleware());

    $group->get('/themes', \App\Controllers\ThemeController::class . ':getThemes')
    ->add(new \App\Middleware\RoleMiddleware('admin'))
    ->add(new \App\Middleware\AuthMiddleware());

    $group->get('/users', \App\Controllers\AuthController::class . ':getUsers')
    ->add(new \App\Middleware\RoleMiddleware('admin'))
    ->add(new \App\Middleware\AuthMiddleware());

    $group->get('/user/{user_id}/themes', \App\Controllers\ThemeController::class . ':getThemesByUserId')->add(new \App\Middleware\AuthMiddleware());

    $group->post('/user/themes/assign', \App\Controllers\ThemeController::class . ':assignThemeToUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->post('/user/themes/unassign', \App\Controllers\ThemeController::class . ':unassignThemeFromUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->put('/user/{id}', \App\Controllers\AuthController::class . ':updateUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->delete('/user/{id}', \App\Controllers\AuthController::class . ':deleteUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());
});

$app->group('/videos', function (RouteCollectorProxy $group) {
    $group->get('/{video_name}/{token}', \App\Controllers\VideosController::class . ':getVideo');
});

$app->get('/test', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Slim Framework is working great");
    return $response;
});

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->run();