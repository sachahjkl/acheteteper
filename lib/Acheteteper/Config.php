<?php

namespace Acheteteper;

use Closure;

/**
 * Configuration class for the framework.
 * 
 * @package Acheteteper
 */
class Config
{
    private ?bool $debug = null;
    /**
     * Supported view file extensions, in order of preference.
     * 
     * @var array<string>
     */
    public static array $viewExtensions = [
        "phtml",
        "php",
        "html",
    ];



    /**
     * @param string $viewDir Directory path where view files are located.
     * @param string $dbPath SQLite database file path.
     */
    public function __construct(
        private string $viewDir = "views",
        private string $dbPath = "database.db",
        private array $userConfigs = [],
        private ?Closure $debugResolver = null,
        private bool $csrfProtection = false,
        private array $allowedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE', 'CONNECT', 'QUERY'],
        private ?string $publicUrl = null,
        private array $trustedProxies = [],
        private int $maxJsonBodyBytes = 1048576,
        private bool $staticDirectoryListing = false
    ) {
        if ($this->debugResolver == null) {
            $this->debugResolver = fn() => false;
        }
    }

    /**
     * Sets a user-defined configuration value.
     * 
     * @param string $key Configuration key.
     * @param mixed $value Configuration value.
     * @return self
     */
    public function setUserConfig(string $key, mixed $value): self
    {
        $this->userConfigs[$key] = $value;
        return $this;
    }

    /**
     * Gets a user-defined configuration value.
     * 
     * @param string $key Configuration key.
     * @return mixed Configuration value, or null if key does not exist.
     */
    public function getUserConfig(string $key): mixed
    {
        return $this->userConfigs[$key] ?? null;
    }

    /**
     * Removes a user-defined configuration value.
     * 
     * @param string $key Configuration key to remove.
     * @return self
     */
    public function clearUserConfig($key): self
    {
        unset($this->userConfigs[$key]);
        return $this;
    }

    /**
     * Removes all user-defined configuration values.
     * 
     * @return self
     */
    public function clearUserConfigs(): self
    {
        $this->userConfigs = [];
        return $this;
    }

    /**
     * Returns whether debug mode is enabled.
     * 
     * @return bool True if debug mode is enabled, false otherwise.
     */
    public function debug(): bool
    {
        return $this->debug ??= ($this->debugResolver)();
    }

    /**
     * Returns the view directory path.
     * 
     * @return string View directory path.
     */
    public function viewDir(): string
    {
        return $this->viewDir;
    }

    /**
     * Returns the database path.
     * 
     * @return string Database path.
     */
    public function dbPath(): string
    {
        return $this->dbPath;
    }

    public function csrfProtection(): bool
    {
        return $this->csrfProtection;
    }

    public function allowedMethods(): array { return $this->allowedMethods; }
    public function publicUrl(): ?string { return $this->publicUrl; }
    public function trustedProxies(): array { return $this->trustedProxies; }
    public function maxJsonBodyBytes(): int { return $this->maxJsonBodyBytes; }
    public function staticDirectoryListing(): bool { return $this->staticDirectoryListing; }
}
