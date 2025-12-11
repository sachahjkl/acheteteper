<?php

namespace Acheteteper;

use Acheteteper\Utils\PathUtils;

/**
 * Builder for creating Config instances.
 * 
 * @package Acheteteper
 */
class ConfigBuilder
{
    private Config $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * Set the view directory path.
     * 
     * @param string $viewDir Directory path where view files are located.
     * @return self
     */
    public function setViewDir(string $viewDir)
    {
        $this->config->viewDir = PathUtils::realpath($viewDir);
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
        $this->config->dbPath = $dbPath;
        return $this;
    }

    public function enableDebug()
    {
        $this->config->debug = true;
        return $this;
    }

    public function disableDebug()
    {
        $this->config->debug = false;
        return $this;
    }

    public function setUserConfig(string $key, mixed $value)
    {
        $this->config->setUserConfig($key, $value);
        return $this;
    }

    public function getUserConfig(string $key)
    {
        return $this->config->getUserConfig($key);
    }

    public function clearUserConfig($key)
    {
        $this->config->clearUserConfig($key);
        return $this;
    }

    public function clearUserConfigs()
    {
        $this->config->clearUserConfigs();
        return $this;
    }

    /**
     * Build and return the configured Config instance.
     * 
     * @return Config
     */
    public function build()
    {
        return $this->config;
    }
}
