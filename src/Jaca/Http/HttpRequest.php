<?php

namespace Jaca\Http;

class HttpRequest
{
    protected array $get;
    protected array $post;
    protected array $server;
    protected array $files;
    protected array $cookie;

    public function __construct()
    {
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->server = $_SERVER;
        $this->files  = $_FILES;
        $this->cookie = $_COOKIE;
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
}
