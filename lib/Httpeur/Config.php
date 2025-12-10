<?php

namespace Httpeur;

/**
 * Configuration class for the framework.
 * 
 * @package Httpeur
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
     */
    public function __construct(
        public string $viewDir = "views"
    ) {}
}
