<?php

namespace Acheteteper;

/**
 * Simple request wrapper for accessing HTTP request data.
 * 
 * @package Acheteteper
 */
class Request
{
    private array $post;
    private array $get;
    private array $server;
    private ?string $body;

    /**
     * Create a Request instance from current PHP globals.
     * 
     * @return self
     */
    public static function fromGlobals(?Config $config = null): self
    {
        return new self($_POST, $_GET, $_SERVER, null, $config);
    }

    /**
     * @param array $post POST data.
     * @param array $get GET data.
     * @param array $server SERVER data.
     */
    public function __construct(array $post = [], array $get = [], array $server = [], ?string $body = null, private ?Config $config = null)
    {
        $this->post = $post;
        $this->get = $get;
        $this->server = $server;
        $this->body = $body;
    }

    /**
     * Get the HTTP request method (GET, POST, etc.).
     * 
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
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

    public function isOptions(): bool
    {
        return $this->method() === 'OPTIONS';
    }

    public function isTrace(): bool
    {
        return $this->method() === 'TRACE';
    }

    public function isConnect(): bool
    {
        return $this->method() === 'CONNECT';
    }

    public function isQuery(): bool
    {
        return $this->method() === 'QUERY';
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isSafe(): bool
    {
        return in_array($this->method(), ['GET', 'HEAD', 'OPTIONS', 'TRACE', 'QUERY'], true);
    }

    public function body(): string
    {
        if ($this->body === null) {
            $this->body = (string) file_get_contents('php://input');
        }
        return $this->body;
    }

    public function json(): mixed
    {
        if (strlen($this->body()) > ($this->config?->maxJsonBodyBytes() ?? 1048576)) {
            throw new HttpException(413, 'JSON body is too large');
        }
        try {
            return json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new HttpException(400, 'Invalid JSON body', $e);
        }
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
        $serverName = strtoupper($name);
        $key = in_array($serverName, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)
            ? $serverName
            : 'HTTP_' . $serverName;
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
        if ($this->config?->publicUrl() !== null) {
            return $this->config->publicUrl() . ($this->server['REQUEST_URI'] ?? '/');
        }
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
        $remote = $this->server['REMOTE_ADDR'] ?? '';
        if (in_array($remote, $this->config?->trustedProxies() ?? [], true)) {
            return trim(explode(',', $this->server['HTTP_X_FORWARDED_FOR'] ?? $remote)[0]);
        }
        return $remote;
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
