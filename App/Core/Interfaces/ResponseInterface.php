<?php

namespace App\Core\Interfaces;

interface ResponseInterface
{
    public function setStatusCode(int $statusCode): void;
    public function setHeaders(array $headers): void;
    public function setContent(string $content): void;
    public function getStatusCode(): int;
    public function getHeaders(): array;
    public function getContent(): string;
}