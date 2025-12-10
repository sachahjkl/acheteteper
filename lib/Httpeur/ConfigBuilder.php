<?php

namespace Httpeur;

class ConfigBuilder
{

    private $config;
    public function __construct()
    {
        $this->config = new Config();
    }


    public function setViewDir(string $viewDir)
    {
        $this->config->viewDir = $viewDir;
        return $this;
    }

    public function build()
    {
        return $this->config;
    }
}
