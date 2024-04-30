<?php

namespace App\Core\Interfaces;

interface MiddlewareInterface
{
    public function process(RequestInterface $request, ResponseInterface $response, callable $next);
}