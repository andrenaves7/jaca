<?php

namespace Jaca\Http;

/**
 * Class HttpRequest
 *
 * Handles HTTP request data abstraction, including GET, POST, SERVER, COOKIE, and FILES.
 * Provides utility methods to access request input, headers, and request metadata.
 *
 * @package Jaca\Http
 */
class HttpRequest
{
    /**
     * @var array The GET parameters.
     */
    protected array $get;

    /**
     * @var array The POST parameters.
     */
    protected array $post;

    /**
     * @var array The SERVER parameters.
     */
    protected array $server;

    /**
     * @var array The FILES parameters.
     */
    protected array $files;

    /**
     * @var array The COOKIE parameters.
     */
    protected array $cookie;

    /**
     * @var array The parsed HTTP headers.
     */
    private array $headers = [];

    /**
     * HttpRequest constructor.
     * Initializes the request data from global arrays and fetches headers.
     */
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookie = $_COOKIE;
        $this->headers = $this->fetchHeaders();
    }

    /**
     * Get a GET parameter value or the entire GET array.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(?string $key = null, $default = null): mixed
    {
        return $key === null ? $this->get : ($this->get[$key] ?? $default);
    }

    /**
     * Get a POST parameter value or the entire POST array.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(?string $key = null, $default = null): mixed
    {
        return $key === null ? $this->post : ($this->post[$key] ?? $default);
    }

    /**
     * Get a file upload by key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get a cookie value.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function cookie(string $key, $default = null): mixed
    {
        return $this->cookie[$key] ?? $default;
    }

    /**
     * Get the HTTP request method.
     *
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get the full request URI.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Get the request path without query string.
     *
     * @return string
     */
    public function path(): string
    {
        return trim(parse_url($this->uri(), PHP_URL_PATH), '/');
    }

    /**
     * Get all input data (GET and POST merged).
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Check if a given key exists in GET or POST.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    /**
     * Get a value from POST or GET, prioritizing POST.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * Check if the request was made via Ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * Retrieve all request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header by name (case-insensitive).
     *
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
        return $this->headers[$name] ?? null;
    }

    /**
     * Check if the request method is POST.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if the request method is GET.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Get all POST data.
     *
     * @return array
     */
    public function getPost(): array
    {
        return $this->post;
    }

    /**
     * Internal method to fetch HTTP request headers.
     *
     * @return array
     */
    private function fetchHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                return $headers;
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = substr($key, 5);
                $header = str_replace('_', ' ', strtolower($header));
                $header = ucwords($header);
                $header = str_replace(' ', '-', $header);
                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $header = str_replace('_', '-', ucwords(strtolower($key), '_'));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}
