<?php

namespace Acheteteper;

/**
 * Configuration class for the framework.
 * 
 * @package Acheteteper
 */
class Config
{
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

    public array $userConfigs = [];

    /**
     * @param string $viewDir Directory path where view files are located.
     * @param string $dbPath SQLite database file path.
     */
    public function __construct(
        public string $viewDir = "views",
        public string $dbPath = "database.sqlite",
        public bool $debug = false
    ) {}


    public function setUserConfig(string $key, mixed $value)
    {
        $this->userConfigs[$key] = $value;
        return $this;
    }

    public function getUserConfig(string $key)
    {
        return $this->userConfigs[$key] ?? null;
    }

    public function clearUserConfig($key)
    {
        unset($this->userConfigs[$key]);
        return $this;
    }

    public function clearUserConfigs()
    {
        $this->userConfigs = [];
        return $this;
    }
}
