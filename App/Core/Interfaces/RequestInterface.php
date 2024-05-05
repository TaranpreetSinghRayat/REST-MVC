<?php

namespace App\Core\Interfaces;

interface RequestInterface
{
    public function getHeaders(): array;
    public function getHeader(string $headerName): ?string;
    public function getCookies(): array;
    public function getBody(): string;
    public function getMethod(): string;
    public function getUri(): string;
}