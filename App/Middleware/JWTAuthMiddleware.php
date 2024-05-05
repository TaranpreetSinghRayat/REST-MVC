<?php

namespace App\Middleware;

use App\Core\Interfaces\MiddlewareInterface;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Core\JwtHandler;

class JWTAuthMiddleware implements MiddlewareInterface
{
    private $jwtHandler;

    public function __construct(JwtHandler $jwtHandler)
    {
        $this->jwtHandler = $jwtHandler;
    }

    public function process(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // Check if JWT_SECRET_KEY is set
        if (!isset($_ENV['JWT_SECRET_KEY'])) {
            // Output a message to run the console command
            echo "JWT_SECRET_KEY is not set. Please run the console command: php console token:secret\n";
            exit; // Terminate the script
        }

        //$authHeader = $request->getHeader('Token');
        //$token = str_replace('Bearer ', '', $authHeader);
        $token = $request->getHeader('Token') ?? $request->getHeader('HTTP_TOKEN');

        try {
            $decoded = $this->jwtHandler->validateToken($token);
            // If the token is valid, proceed to the next middleware or controller
            return $next($request, $response);
        } catch (\Exception $e) {
            // If the token is invalid, return an unauthorized response
            $response->setStatusCode(401);
            $response->setContent(['error' => 'Invalid Token']);
            return $response;
        }
    }
}
