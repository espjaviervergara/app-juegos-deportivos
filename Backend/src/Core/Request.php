<?php
namespace App\Core;

class Request
{
    public string $method;
    public string $uri;
    public string $path;
    public array $query;
    public array $headers;
    public $body;
    public array $params = [];
    public ?array $user = null; // JWT payload

    public function __construct()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // X-HTTP-Method-Override for hosting básico
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $override = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            if (in_array($override, ['PUT','DELETE','PATCH'])) {
                $method = $override;
            }
        }
        $this->method = $method;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->uri = $uri;
        $this->path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $this->query = $_GET ?? [];
        $this->headers = $this->parseHeaders();
        $this->body = $this->parseBody();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($k, 5)));
                $headers[$name] = $v;
            } elseif (in_array($k, ['CONTENT_TYPE','CONTENT_LENGTH'])) {
                $headers[str_replace('_','-', strtolower($k))] = $v;
            }
        }
        return $headers;
    }

    private function parseBody()
    {
        $ct = $this->headers['content-type'] ?? '';
        $raw = file_get_contents('php://input');
        if (str_contains($ct, 'application/json') && $raw !== '') {
            $decoded = json_decode($raw, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        if (!empty($_POST)) return $_POST;
        if ($raw === '') return null;
        return $raw;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function query(string $key, $default = null) { return $this->query[$key] ?? $default; }
    public function input(string $key, $default = null) { return is_array($this->body) ? ($this->body[$key] ?? $default) : $default; }
    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) return substr($auth, 7);
        return null;
    }
    public function cookie(string $name): ?string { return $_COOKIE[$name] ?? null; }
}
