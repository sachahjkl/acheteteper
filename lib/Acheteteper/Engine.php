<?php

namespace Acheteteper;

use Acheteteper\Utils\DebugUtils;
use Acheteteper\Utils\StringUtils;

/**
 * Engine class
 * 
 * This class is the core of the application and coordinates the request/response cycle.
 * 
 * @package Acheteteper
 * @author  sachahjkl
 * @version 1.0.0
 */
class Engine
{
    /**
     * @var array<string, string> Mapping of route paths to controller class names.
     */
    private $controllerMappings = [];

    /**
     * @var array<string, callable> Datasource factories keyed by name.
     */
    private array $datasourceFactories = [];

    private string $defaultDatasource = 'default';

    /**
     * @var array<string, callable> Service factories keyed by class name.
     */
    private array $serviceFactories = [];

    /**
     * @var array<string, callable> Repository factories keyed by class name.
     */
    private array $repositoryFactories = [];

    /**
     * @param Config $config Framework configuration.
     */
    public function __construct(private Config $config) {}

    /**
     * Register a controller for a given route path.
     * 
     * @param string $path Route path (e.g., '/', '/about').
     * @param string $class Fully qualified controller class name.
     * @return void
     */
    public function registerController(string $path, string $class)
    {
        $this->controllerMappings[$path] = $class;
    }

    /**
     * Process the current HTTP request and route it to the appropriate controller action.
     * 
     * Routing logic:
     * - First attempts to match the full path to a registered controller
     * - If not found, parses the path as /controller/action
     * - Returns 404 if controller or action method is not found
     * 
     * @return void
     */
    public function run()
    {
        $path = $this->pathname();
        $action = "index";

        $controller = $this->tryFindController($path);

        if ($controller === null) {
            $parsedPath = $this->parsePath($path);
            $action = $parsedPath["actionRoute"];
            $controllerRoute = $parsedPath["controllerRoute"];
            $controller = $this->tryFindController($controllerRoute);
        }

        if ($controller === null) {
            $this->notFound();
            return;
        }

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        try {
            $controller->$action();
        } catch (HttpException $e) {
            $this->setStatus($e->getStatus());
            echo $e->getMessage();
            return;
        } catch (\Throwable $e) {

            $this->setStatus(500);
            echo "500 Internal Server Error";
            echo "<br>";
            echo "<br>";
            echo "Debug mode: <b>" . ($this->config->debug ? "enabled" : "disabled") . "</b>";
            if ($this->config->debug) {
                echo "<br>";
                echo "<br>";
                echo "<b>Message:</b> <code style='background-color: #f0f0f0; padding: 5px; border-radius: 5px;'>" . $e->getMessage() . "</code>";
                echo "<br>";
                echo "<br>";
                echo "<b>Trace:</b> 
                <pre style='background-color: #f0f0f0; padding: 5px; border-radius: 5px;'>" . $e->getTraceAsString() . "</pre>";
                echo "<br>";
                echo "<br>";
                DebugUtils::dump($e);
            }
            return;
        }
    }

    /**
     * Register a datasource factory.
     * 
     * @param string $name
     * @param callable|string $factory callable(Config): DataSourceInterface or class-string
     * @return void
     */
    public function registerDatasource(string $name, callable|string $factory): void
    {
        if (is_string($factory)) {
            $class = $factory;
            $factory = fn(Config $config) => new $class($config);
        }
        $this->datasourceFactories[$name] = $factory;
    }

    /**
     * Set the default datasource name.
     * 
     * @param string $name
     * @return void
     */
    public function setDefaultDatasource(string $name): void
    {
        $this->defaultDatasource = $name;
    }

    /**
     * Register a service factory.
     * 
     * @param string $class Service class name.
     * @param callable|string|null $factory callable(Config, callable, callable, callable): object or class-string. If null, class name is used.
     * @return void
     */
    public function registerService(string $class, callable|string|null $factory = null): void
    {
        if ($factory === null) {
            $factory = $class;
        }
        if (is_string($factory)) {
            $className = $factory;
            $factory = function (Config $config, callable $getDatasource, callable $getService, callable $getRepository) use ($className) {
                return new $className($config, $getDatasource, $getService, $getRepository);
            };
        }
        $this->serviceFactories[$class] = $factory;
    }

    /**
     * Register a repository factory.
     * 
     * @param string $class Repository class name.
     * @param callable|string|null $factory callable(Config, callable, callable, callable): object or class-string. If null, class name is used.
     * @return void
     */
    public function registerRepository(string $class, callable|string|null $factory = null): void
    {
        if ($factory === null) {
            $factory = $class;
        }
        if (is_string($factory)) {
            $className = $factory;
            $factory = function (Config $config, callable $getDatasource, callable $getService, callable $getRepository) use ($className) {
                return new $className($config, $getDatasource, $getService, $getRepository);
            };
        }
        $this->repositoryFactories[$class] = $factory;
    }


    /**
     * Parse a URL path into controller route and action route.
     * 
     * @param string $path URL path to parse.
     * @return array{controllerRoute: string, actionRoute: string}
     */
    private function parsePath(string $path)
    {
        $parsedPath = [
            "controllerRoute" => "/",
            "actionRoute" => "index",
        ];

        $pathParts = StringUtils::splitPath($path);

        if (isset($pathParts[0]) && !StringUtils::isWhitespaceOrNull($pathParts[0])) {
            $parsedPath["controllerRoute"] = $pathParts[0];
        }

        if (isset($pathParts[1]) && !StringUtils::isWhitespaceOrNull($pathParts[1])) {
            $parsedPath["actionRoute"] = $pathParts[1];
        }

        if (!str_starts_with($parsedPath["controllerRoute"], "/")) {
            $parsedPath["controllerRoute"] = "/" . $parsedPath["controllerRoute"];
        }

        return $parsedPath;
    }

    /**
     * Find and instantiate a controller for the given route.
     * 
     * @param string $route Route path.
     * @return ControllerBase|null Controller instance or null if not found.
     */
    private function tryFindController(string $route)
    {
        if (isset($this->controllerMappings[$route])) {
            $request = Request::fromGlobals();
            $response = Response::fromGlobals();
            $controllerClass = $this->controllerMappings[$route];
            $controller = new $controllerClass($this->config, $request, $response);

            // Per-request caches
            $datasourceCache = [];
            $serviceCache = [];
            $repositoryCache = [];

            $datasourceFactory = function (?string $name = null) use (&$datasourceCache) {
                $key = $name ?? $this->defaultDatasource;
                if (!isset($this->datasourceFactories[$key])) {
                    if (!isset($this->datasourceFactories[$this->defaultDatasource])) {
                        throw new HttpException(500, "Datasource not registered: $key");
                    }
                    $key = $this->defaultDatasource;
                }
                if (!isset($datasourceCache[$key])) {
                    $factory = $this->datasourceFactories[$key];
                    $datasourceCache[$key] = $factory($this->config);
                }
                return $datasourceCache[$key];
            };

            // Ensure a default datasource exists; if none registered, use SqliteDataSource.
            if (!isset($this->datasourceFactories[$this->defaultDatasource])) {
                $this->registerDatasource($this->defaultDatasource, SqliteDataSource::class);
            }

            $repositoryResolver = null;
            $serviceResolver = null;

            $serviceResolver = function (string $class) use (&$serviceCache, &$datasourceFactory, &$serviceResolver, &$repositoryResolver) {
                if (!isset($this->serviceFactories[$class])) {
                    if (!class_exists($class)) {
                        throw new HttpException(500, "Service not registered: $class");
                    }
                    $this->registerService($class);
                }
                if (!isset($serviceCache[$class])) {
                    $factory = $this->serviceFactories[$class];
                    $serviceCache[$class] = $factory($this->config, $datasourceFactory, $serviceResolver, $repositoryResolver);
                }
                return $serviceCache[$class];
            };

            $repositoryResolver = function (string $class) use (&$repositoryCache, &$datasourceFactory, &$serviceResolver, &$repositoryResolver) {
                if (!isset($this->repositoryFactories[$class])) {
                    if (!class_exists($class)) {
                        throw new HttpException(500, "Repository not registered: $class");
                    }
                    $this->registerRepository($class);
                }
                if (!isset($repositoryCache[$class])) {
                    $factory = $this->repositoryFactories[$class];
                    $repositoryCache[$class] = $factory($this->config, $datasourceFactory, $serviceResolver, $repositoryResolver);
                }
                return $repositoryCache[$class];
            };

            if (method_exists($controller, 'setDatasourceProvider')) {
                $controller->setDatasourceProvider($datasourceFactory);
            }
            if (method_exists($controller, 'setServiceProvider')) {
                $controller->setServiceProvider($serviceResolver);
            }
            if (method_exists($controller, 'setRepositoryProvider')) {
                $controller->setRepositoryProvider($repositoryResolver);
            }
            if (property_exists($controller, 'datasource')) {
                $controller->datasource = $datasourceFactory($this->defaultDatasource);
            }

            return $controller;
        } else {
            return null;
        }
    }

    /**
     * Get the pathname from the current request URI.
     * 
     * @return string URL path.
     */
    private function pathname()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Send a 404 Not Found response.
     * 
     * @return void
     */
    private function notFound()
    {
        $this->setStatus(404);
        echo "404 Not Found";
        die();
    }

    /**
     * Set the HTTP response status code.
     * 
     * @param int $status HTTP status code.
     * @return void
     */
    private function setStatus(int $status)
    {
        http_response_code($status);
    }
}
