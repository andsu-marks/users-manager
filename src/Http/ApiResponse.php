<?php
namespace Src\Http;

use Psr\Http\Message\responseInterface as Response;

/**
 * Utility class for building standardized JSON API responses.
 * 
 * Provides helper methods for returning success and error responses in a consistent JSON format
 * across the application.
 */
class ApiResponse {
    /**
     * Returns a standardized success JSON response.
     * 
     * Writes a JSON body containing a 'success' flag and returned 'data'. Allows custom HTTP status codes.
     * 
     * @param Response $response HTTP response object.
     * @param array $data Payload to be returned in the response body.
     * @param int $status HTTP status code (default: 200).
     * 
     * @return Response Response object with JSON body and proper headers.
     */
    public static function success(Response $response, array $data = [], int $status = 200): Response {
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /**
     * Returns a standardized error JSON response.
     * 
     * Writes a JSON body containing a 'success' flag set to false and an 'error' message describing what went wrong.
     * 
     * @param Response $response HTTP response object.
     * @param string $message Error message.
     * @param int $status HTTP status code (default: 400).
     * 
     * @return Response Response object with JSON body and proper headers.
     */
    public static function error(Response $response, string $message, int $status = 400): Response {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}