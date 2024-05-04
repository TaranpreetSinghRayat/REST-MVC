<?php

namespace App\Middleware;

use App\Core\Interfaces\MiddlewareInterface;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // Check if the user is authenticated
        if (!$this->isAuthenticated()) {
            // If not authenticated, redirect to login or return an unauthorized response
            $response->setContent('Unauthorized');
            $response->setStatusCode(401); // Unauthorized
            return $response;
        }

        // If authenticated, proceed to the next middleware or controller
        return $next($request, $response);
    }

    private function isAuthenticated()
    {

        return true; // Placeholder: return true if authenticated, false otherwise
    }
}
