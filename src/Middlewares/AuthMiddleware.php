<?php
namespace Src\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseFactoryInterface;

class AuthMiddleware {
    private string $secret;
    private ResponseFactoryInterface $response;

    public function __construct(ResponseFactoryInterface $response) {
        $this->secret = $_ENV['SECRET_KEY'];
        $this->response = $response;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $authHeader = $request->getHeader('Authorization')[0] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            $response = $this->response->createresponse(401);
            $response->getBody()->write(json_encode(['error' => 'Token missing!']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $token = substr($authHeader, 7);
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            $request = $request->withAttribute('userId', $decoded->sub);
            return $handler->handle($request);
        } catch (\Exception $error) {
            $response = $this->response->createResponse(401);
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    } 
}