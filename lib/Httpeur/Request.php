<?php

namespace Httpeur;

/**
 * Simple request wrapper for accessing HTTP request data.
 * 
 * @package Httpeur
 */
class Request
{
    private array $post;
    private array $get;
    private array $server;

    /**
     * Create a Request instance from current PHP globals.
     * 
     * @return self
     */
    public static function fromGlobals(): self
    {
        return new self($_POST, $_GET, $_SERVER);
    }

    /**
     * @param array $post POST data.
     * @param array $get GET data.
     * @param array $server SERVER data.
     */
    public function __construct(array $post = [], array $get = [], array $server = [])
    {
        $this->post = $post;
        $this->get = $get;
        $this->server = $server;
    }

    /**
     * Get the HTTP request method (GET, POST, etc.).
     * 
     * @return string
     */
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
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
     * Check if the request method is POST.
     * 
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if the request method is PUT.
     * 
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    /**
     * Check if the request method is DELETE.
     * 
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    /**
     * Check if the request method is PATCH.
     * 
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method() === 'PATCH';
    }

    /**
     * Check if the request method is HEAD.
     * 
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->method() === 'HEAD';
    }

    /**
     * Get a value from POST data.
     * 
     * @param string $key Field name.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get a value from GET data.
     * 
     * @param string $key Field name.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get a value from POST or GET data (POST takes precedence).
     * 
     * @param string $key Field name.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * Get all POST data.
     * 
     * @return array
     */
    public function allPost(): array
    {
        return $this->post;
    }

    /**
     * Get all GET data.
     * 
     * @return array
     */
    public function allGet(): array
    {
        return $this->get;
    }

    /**
     * Get a request header value.
     * 
     * @param string $name Header name (case-insensitive).
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function header(string $name, mixed $default = null): mixed
    {
        $name = strtolower($name);
        $name = str_replace('-', '_', $name);
        $key = 'HTTP_' . strtoupper($name);
        return $this->server[$key] ?? $default;
    }

    /**
     * Get the request URI path.
     * 
     * @return string
     */
    public function path(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';
    }

    /**
     * Get the full request URL.
     * 
     * @return string
     */
    public function url(): string
    {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    /**
     * Get the request IP address.
     * 
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            return $this->server['HTTP_X_FORWARDED_FOR'];
        }
        return $this->server['REMOTE_ADDR'] ?? '';
    }

    /**
     * Check if request is AJAX (XMLHttpRequest).
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}
