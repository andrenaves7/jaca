<?php

namespace Jaca\Http;

class HttpRequest
{
    protected array $get;
    protected array $post;
    protected array $server;
    protected array $files;
    protected array $cookie;

    private array $headers = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookie = $_COOKIE;
        $this->headers = $this->fetchHeaders();
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookie[$key] ?? $default;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        return trim(parse_url($this->uri(), PHP_URL_PATH), '/');
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function fetchHeaders(): array
    {
        // Se a função nativa existir, usa ela direto
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                return $headers;
            }
        }

        // Fallback: constrói os headers a partir de $_SERVER
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                // Remove o prefixo HTTP_
                $header = substr($key, 5);
                // Troca underscores por hífens e deixa cada palavra capitalizada
                $header = str_replace('_', ' ', strtolower($header));
                $header = ucwords($header);
                $header = str_replace(' ', '-', $header);

                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                // Alguns headers não começam com HTTP_
                $header = str_replace('_', '-', ucwords(strtolower($key), '_'));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        // Case-insensitive search for header
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
        return $this->headers[$name] ?? null;
    }
}
