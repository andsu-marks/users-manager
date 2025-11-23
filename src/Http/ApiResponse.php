<?php
namespace Src\Http;

use Psr\Http\Message\responseInterface as Response;

class ApiResponse {
    public static function success(Response $response, array $data = [], int $status = 200): Response {
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public static function error(Response $response, string $message, int $status = 400): Response {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}