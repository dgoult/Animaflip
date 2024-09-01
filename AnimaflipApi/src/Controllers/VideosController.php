<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;
use App\Models\User;

class VideosController
{
    public function getVideo(Request $request, Response $response, array $args): Response
    {
        // Récupérer le nom de la vidéo et le token à partir des arguments de l'URL
        $videoName = $args['video_name'];
        $token = $args['token'];

        // Définir le chemin du fichier vidéo
        $filePath = __DIR__ . '/../Videos/' . $videoName . '.mp4';
            
        file_put_contents('debugVideosController.txt', print_r('$token', TRUE));
        
        // Vérifier et décoder le token JWT
        try {
            $secret = $_ENV['JWT_SECRET'];
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));

            // Vous pouvez également récupérer l'utilisateur à partir de l'ID décodé
            $user = User::getById($decoded->id);

            // Vérification de l'existence du fichier vidéo
            if (file_exists($filePath)) {
                $videoStream = fopen($filePath, 'rb');
                $response = $response->withHeader('Content-Type', 'video/mp4')
                                     ->withHeader('Content-Disposition', 'inline; filename="' . $videoName . '.mp4"');
                $response->getBody()->write(stream_get_contents($videoStream));
                fclose($videoStream);
                return $response;
            } else {
                return $response->withStatus(404, 'Video Not Found');
            }
        } catch (\Exception $e) {
            // Si le token est invalide ou une autre erreur se produit
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}