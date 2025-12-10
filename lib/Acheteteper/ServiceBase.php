<?php

namespace Acheteteper;

abstract class ServiceBase
{
    /**
     * @param Config $config
     * @param callable $datasourceResolver fn(string $name='default'): DataSourceInterface
     * @param callable $serviceResolver fn(string $class): object
     * @param callable $repositoryResolver fn(string $class): object
     */
    public function __construct(
        protected Config $config,
        private $datasourceResolver,
        private $serviceResolver,
        private $repositoryResolver
    ) {}

    protected function datasource(string $name = 'default'): DataSourceInterface
    {
        return ($this->datasourceResolver)($name);
    }

    protected function getService(string $class): object
    {
        return ($this->serviceResolver)($class);
    }

    protected function getRepository(string $class): object
    {
        return ($this->repositoryResolver)($class);
    }
}
