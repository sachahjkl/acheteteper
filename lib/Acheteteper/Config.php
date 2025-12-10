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

    /**
     * @param string $viewDir Directory path where view files are located.
     * @param string $dbPath SQLite database file path.
     */
    public function __construct(
        public string $viewDir = "views",
        public string $dbPath = "database.sqlite",
        public bool $debug = false
    ) {}
}
