<?php
namespace Src\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Src\Http\ApiResponse;

/**
 * Middleware responsible for validating JWT authentication tokens.
 * 
 * Checks the authorization header, validates the Bearer token using the configured secret key, and attaches
 * the authenticathed user's ID to the request attributes. Returns a 401 JSON error when authentication fails.
 */
class AuthMiddleware {
    /**
     * Secret key used to validates JWT signatures.
     * 
     * @var string
     */
    private string $secret;

    /**
     * Factory used to create new PSR-7 Response instances.
     * 
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $response;

    /**
     * AuthMidleware constructor.
     * 
     * Loads the JWT secret key from environment variables and stores the response factory for
     * generating JSON responses.
     * 
     * @param ResponseFactoryInterface $response Factory for creating responses.
     */
    public function __construct(ResponseFactoryInterface $response) {
        $this->secret = $_ENV['SECRET_KEY'];
        $this->response = $response;
    }

    /**
     * Handles the authentication chack for incoming requests.
     * 
     * Validates the authorization header, extracts and decodes the JWT token, and attaches the authenticated user's 
     * ID ('sub') to the request. If the token is missing or invalid, returns a JSON error with status 401.
     * 
     * @param Request $request Incoming HTTP request.
     * @param RequestHandler $handler Next handler in the middleware chain.
     * 
     * @return Response JSON error response or the result of the next handler.
     * 
     * @throws \Exception When token decoding fails (caught internally).
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $authHeader = $request->getHeader('Authorization')[0] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            $response = $this->response->createResponse();
            return ApiResponse::error($response, 'Token missing!', 401);
        }

        $token = substr($authHeader, 7);
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            $request = $request->withAttribute('userId', $decoded->sub);
            return $handler->handle($request);
        } catch (\Exception $error) {
            $response = $this->response->createResponse();
            return ApiResponse::error($response, $error->getMessage(), 401);
        }
    } 
}