<?php

namespace Httpeur;

class Config
{
    public static array $viewExtensions = [
        "phtml",
        "php",
        "html",
    ];

    public function __construct(
        public string $viewDir = "views"
    ) {}
}
