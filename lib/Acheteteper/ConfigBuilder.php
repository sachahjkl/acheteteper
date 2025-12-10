<?php

namespace Acheteteper;

/**
 * Builder for creating Config instances.
 * 
 * @package Acheteteper
 */
class ConfigBuilder
{
    private $config;

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
        $this->config->viewDir = $viewDir;
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
