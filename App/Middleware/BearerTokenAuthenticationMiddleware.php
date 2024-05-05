<?php

namespace App\Middleware;

use App\Core\Interfaces\MiddlewareInterface;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;

class BearerTokenAuthenticationMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, ResponseInterface $response, callable $next)
    {

        $authHeader = $request->getHeader('HTTP_AUTHORIZATION') ?? $request->getHeader('AUTHORIZATION');
        if (!$authHeader || !preg_match('/^Bearer (\S+)$/', $authHeader, $matches)) {
            $response->setContent('Unauthorized');
            $response->setStatusCode(401); // Unauthorized
            return $response;
        }

        $token = $matches[1];
        if (!$this->isValidToken($token)) {
            $response->setContent('Unauthorized: Invalid Token');
            $response->setStatusCode(401); // Unauthorized
            return $response;
        }

        // If authenticated, proceed to the next middleware or controller
        return $next($request, $response);
    }

    private function isValidToken($token)
    {
        //Get token from users table and validated
        $conn = \App\Core\Database::getConnection();

        // Query the users table for a user with the provided token
        $queryBuilder = $conn->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('users')
            ->where('auth_token = :token')
            ->setParameter('token', $token);

        $user = $queryBuilder->executeQuery()->fetchAssociative();

        // Check if a user was found and if the token matches
        if ($user && $user['auth_token'] === $token) {
            return true; // Token is valid
        } else {
            return false; // Token is invalid or not found
        }
    }
}
