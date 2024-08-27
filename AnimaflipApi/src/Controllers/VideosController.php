<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VideosController
{
    public function getVideo(Request $request, Response $response, array $args): Response
    {
        $videoName = $args['video_name'];
        $filePath = __DIR__ . '/../Videos/' . $videoName . '.mp4';

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
    }
}