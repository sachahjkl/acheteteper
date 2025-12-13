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
        $this->viewDir = PathUtils::realpath($viewDir);
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

    /**
     * Build and return the configured Config instance.
     * 
     * @return Config
     */
    public function build()
    {
        return new Config($this->viewDir, $this->dbPath,  $this->userConfigs, $this->debugResolver);
    }
}
