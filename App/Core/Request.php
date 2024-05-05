<?php

namespace App\Core;

use App\Core\Interfaces\RequestInterface;

class Request implements RequestInterface
{
    protected $headers;
    protected $cookies;
    protected $body;
    protected $method;
    protected $uri;

    public function __construct(array $headers, array $cookies, string $body, string $method, string $uri)
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->body = $body;
        $this->method = $method;
        $this->uri = $uri;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $headerName): ?string
    {
        // Convert header name to lowercase to ensure case-insensitive matching
        $headerName = strtolower($headerName);

        // Search through the headers array
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $headerName) {
                return $value;
            }
        }

        return null;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
