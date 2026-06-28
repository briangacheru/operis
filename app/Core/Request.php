<?php
declare(strict_types=1);

/**
 * Request — immutable wrapper around the current HTTP request.
 */
class Request
{
    private string $method;
    private string $uri;
    private array  $query;
    private array  $post;
    private array  $files;
    private array  $server;
    private array  $cookies;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri     = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query   = $_GET;
        $this->post    = $_POST;
        $this->files   = $_FILES;
        $this->server  = $_SERVER;
        $this->cookies = $_COOKIE;
    }

    public function method(): string { return $this->method; }
    public function uri(): string    { return $this->uri; }

    public function isGet(): bool    { return $this->method === 'GET'; }
    public function isPost(): bool   { return $this->method === 'POST'; }
    public function isPatch(): bool  { return $this->method === 'PATCH'; }
    public function isPut(): bool    { return $this->method === 'PUT'; }
    public function isDelete(): bool { return $this->method === 'DELETE'; }
    public function isAjax(): bool   { return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }

    /** GET parameter */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** POST parameter */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->json($key) ?? $default;
    }

    /** All POST + JSON body merged */
    public function all(): array
    {
        return array_merge($this->post, $this->jsonBody() ?? []);
    }

    /** Decoded JSON body (for Content-Type: application/json requests) */
    public function jsonBody(): ?array
    {
        if ($this->jsonBody === null) {
            $raw = file_get_contents('php://input');
            $this->jsonBody = $raw ? json_decode($raw, true) : [];
        }
        return $this->jsonBody;
    }

    public function json(string $key, mixed $default = null): mixed
    {
        return $this->jsonBody()[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function bearerToken(): ?string
    {
        $header = $this->server['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    /** Route parameters injected by the Router */
    private array $params = [];

    public function setParams(array $params): void { $this->params = $params; }
    public function param(string $key, mixed $default = null): mixed { return $this->params[$key] ?? $default; }
    public function params(): array { return $this->params; }
}
