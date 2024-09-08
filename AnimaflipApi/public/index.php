<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


// Créer l'application Slim
$app = AppFactory::create();

// Créer l'instance Twig et l'ajouter au middleware
$twig = Twig::create(__DIR__ . '/../src/views', [
    'debug' => true,
    'cache' => false, // Désactiver la mise en cache
]);

// Ajouter l'extension de débogage à Twig
$twig->addExtension(new DebugExtension());

// Ajouter le middleware Twig à l'application
$app->add(TwigMiddleware::create($app, $twig));

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/login', \App\Controllers\AuthController::class . ':login');

    $group->get('/user/{user_id}/themes', \App\Controllers\ThemeController::class . ':getThemesByUserId')
    ->add(new \App\Middleware\AuthMiddleware());
    
    $group->post('/register', \App\Controllers\AuthController::class . ':register')
     ->add(new \App\Middleware\RoleMiddleware('admin'))
     ->add(new \App\Middleware\AuthMiddleware());

    $group->get('/themes', \App\Controllers\ThemeController::class . ':getThemes')
    ->add(new \App\Middleware\RoleMiddleware('admin'))
    ->add(new \App\Middleware\AuthMiddleware());

    $group->get('/users', \App\Controllers\AuthController::class . ':getUsers')
    ->add(new \App\Middleware\RoleMiddleware('admin'))
    ->add(new \App\Middleware\AuthMiddleware());

    $group->put('/user/{id}', \App\Controllers\AuthController::class . ':updateUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->delete('/user/{id}', \App\Controllers\AuthController::class . ':deleteUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->post('/user/themes/assign', \App\Controllers\ThemeController::class . ':assignThemeToUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());

    $group->post('/user/themes/unassign', \App\Controllers\ThemeController::class . ':unassignThemeFromUser')
        ->add(new \App\Middleware\RoleMiddleware('admin'))
        ->add(new \App\Middleware\AuthMiddleware());
});

$app->group('/videos', function (RouteCollectorProxy $group) {
    $group->get('/{video_name}/{token}', \App\Controllers\VideosController::class . ':getVideo');
});

// Définir la route avec accès à Twig via le middleware
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) use ($twig) {
    return $twig->render($response, 'login.html.twig', [
        'name' => $args['name']
    ]);
})->setName('profile');


// Pannel Admin Auth
$app->get('/admin/login', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->loginForm($request, $response, $args);
});
$app->post('/admin/login', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->login($request, $response, $args);
});
$app->get('/admin/logout', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->logout($request, $response, $args);
});

// Pannel Admin dashboard
$app->get('/admin/dashboard/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->dashboard($request, $response, $args);
});

// Pannel Admin User CRUD
$app->get('/admin/user/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createUserForm($request, $response, $args);
});
$app->post('/admin/user/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createUser($request, $response, $args);
});
$app->get('/admin/user/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateUserForm($request, $response, $args);
});
$app->post('/admin/user/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateUser($request, $response, $args);
});
$app->post('/admin/user/{id}/delete/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->deleteUser($request, $response, $args);
});

//Affectation ThemeUser
$app->post('/admin/user/{id}/assign-theme/{theme_id}/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->assignTheme($request, $response, $args);
});
$app->post('/admin/user/{id}/unassign-theme/{theme_id}/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->unassignTheme($request, $response, $args);
});

// Pannel Admin Theme CRUD
$app->get('/admin/theme/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createThemeForm($request, $response, $args);
});
$app->post('/admin/theme/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createTheme($request, $response, $args);
});
$app->get('/admin/theme/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateThemeForm($request, $response, $args);
});
$app->post('/admin/theme/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateTheme($request, $response, $args);
});
$app->post('/admin/theme/{id}/delete/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->deleteTheme($request, $response, $args);
});

// Pannel Admin Animation CRUD
$app->get('/admin/animation/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createAnimationForm($request, $response, $args);
});
$app->post('/admin/animation/create/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->createAnimation($request, $response, $args);
});
$app->get('/admin/animation/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateAnimationForm($request, $response, $args);
});
$app->post('/admin/animation/{id}/edit/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->updateAnimation($request, $response, $args);
});
$app->post('/admin/animation/{id}/delete/{token}', function (Request $request, Response $response, array $args) use ($twig) {
    $controller = new \App\Controllers\AdminController($twig);
    return $controller->deleteAnimation($request, $response, $args);
});

// Servir les fichiers statiques (CSS, JS, etc.) à partir du répertoire public
$app->get('/css', function ($request, $response, $args) {
    $filePath = __DIR__ . '/../public/css';
    if (file_exists($filePath)) {
        return $response->write(file_get_contents($filePath))->withHeader('Content-Type', mime_content_type($filePath));
    } else {
        return $response->withStatus(404);
    }
});

$app->get('/test', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Slim Framework is working great");
    return $response;
});

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->run();