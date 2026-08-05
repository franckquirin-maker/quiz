<?php

namespace App\Core;

class Request
{
    public string $method;
    public string $path;
    public array $query = [];
    public array $body = [];
    public array $params = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/') ?: '/';

        parse_str($_SERVER['QUERY_STRING'] ?? '', $this->query);

        $this->body = $this->parseBody();
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
}
