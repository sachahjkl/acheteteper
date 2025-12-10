<?php

namespace Acheteteper;

/**
 * Simple response wrapper.
 * 
 * @package Acheteteper
 */
class Response
{
    private int $status = 200;
    private array $headers = [];
    private string $body = '';

    /**
     * Create a Response instance from current PHP globals.
     * 
     * @return self
     */
    public static function fromGlobals(): self
    {
        return new self();
    }

    /**
     * Set the HTTP status code.
     * 
     * @param int $status HTTP status code.
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get the HTTP status code.
     * 
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set a response header.
     * 
     * @param string $name Header name.
     * @param string $value Header value.
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get a response header.
     * 
     * @param string $name Header name.
     * @return string|null Header value or null if not set.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all response headers.
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the response body.
     * 
     * @param string $body Response body.
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the response body.
     * 
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Send the response (set headers and output body).
     * 
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }

    /**
     * Redirect to a URL.
     * 
     * @param string $url Target URL.
     * @param int $status Redirect status code (default: 302).
     * @return void
     */
    public function redirect(string $url, int $status = 302): void
    {
        $this->setStatus($status);
        $this->setHeader('Location', $url);
        $this->send();
        exit();
    }

    /**
     * Send JSON response.
     * 
     * @param array $data Data to encode as JSON.
     * @param int $status HTTP status code (default: 200).
     * @return void
     */
    public function json(array $data, int $status = 200): void
    {
        $this->setStatus($status);
        $this->setHeader('Content-Type', 'application/json');
        $this->setBody(json_encode($data));
        $this->send();
        exit();
    }
}
