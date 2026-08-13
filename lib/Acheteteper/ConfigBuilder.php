<?php

namespace Acheteteper;

use Acheteteper\Utils\PathUtils;
use Closure;

/**
 * Builder for creating Config instances.
 * 
 * @package Acheteteper
 */
class ConfigBuilder
{
    private ?Closure $debugResolver = null;
    private string $viewDir = "views";
    private string $dbPath = "database.db";
    private array $userConfigs = [];
    private bool $csrfProtection = false;
    private array $allowedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE', 'CONNECT', 'QUERY'];
    private ?string $publicUrl = null;
    private array $trustedProxies = [];
    private int $maxJsonBodyBytes = 1048576;
    private bool $staticDirectoryListing = false;

    public function __construct()
    {
        $this->debugResolver = fn() => false;
    }

    /**
     * Set the view directory path.
     * 
     * @param string $viewDir Directory path where view files are located.
     * @return self
     */
    public function setViewDir(string $viewDir)
    {
        $resolved = PathUtils::realpath($viewDir);
        if ($resolved === false || !is_dir($resolved)) {
            throw new \InvalidArgumentException("View directory not found: $viewDir");
        }
        $this->viewDir = $resolved;
        return $this;
    }

    /**
     * Set SQLite database path.
     * 
     * @param string $dbPath Path to SQLite file.
     * @return self
     */
    public function setDbPath(string $dbPath)
    {
        $this->dbPath = $dbPath;
        return $this;
    }

    public function disableDebug()
    {
        $this->debugResolver = fn() => false;
        return $this;
    }

    public function setUserConfig(string $key, mixed $value): self
    {
        $this->userConfigs[$key] = $value;
        return $this;
    }

    public function setDebugResolver(Closure $debugResolver): self
    {
        $this->debugResolver = $debugResolver;
        return $this;
    }

    public function enableCsrfProtection(): self
    {
        $this->csrfProtection = true;
        return $this;
    }

    public function setAllowedMethods(array $methods): self
    {
        $methods = array_values(array_unique(array_map('strtoupper', $methods)));
        if ($methods === []) {
            throw new \InvalidArgumentException('At least one HTTP method must be allowed');
        }
        $this->allowedMethods = $methods;
        return $this;
    }

    public function setPublicUrl(?string $url): self
    {
        $this->publicUrl = $url === null ? null : rtrim($url, '/');
        return $this;
    }

    public function setTrustedProxies(array $addresses): self
    {
        $this->trustedProxies = array_values($addresses);
        return $this;
    }

    public function setMaxJsonBodyBytes(int $bytes): self
    {
        if ($bytes < 1) {
            throw new \InvalidArgumentException('JSON body limit must be positive');
        }
        $this->maxJsonBodyBytes = $bytes;
        return $this;
    }

    public function enableStaticDirectoryListing(): self
    {
        $this->staticDirectoryListing = true;
        return $this;
    }

    /**
     * Build and return the configured Config instance.
     * 
     * @return Config
     */
    public function build()
    {
        return new Config(
            $this->viewDir,
            $this->dbPath,
            $this->userConfigs,
            $this->debugResolver,
            $this->csrfProtection,
            $this->allowedMethods,
            $this->publicUrl,
            $this->trustedProxies,
            $this->maxJsonBodyBytes,
            $this->staticDirectoryListing
        );
    }
}
