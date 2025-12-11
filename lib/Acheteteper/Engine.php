<?php

namespace Acheteteper;

use Acheteteper\Utils\DebugUtils;
use Acheteteper\Utils\PathUtils;
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
     * @var array<int, array{prefix: string, base: string}> Static directory mappings.
     */
    private array $staticDirectories = [];

    private Timings $timings;

    /**
     * @var Request Current Request instance.
     */
    private Request $request;
    /**
     * @var Response Current Response instance.
     */
    private Response $response;

    /**
     * @param Config $config Framework configuration.
     */
    public function __construct(private Config $config)
    {
        $this->timings = new Timings();
        $this->request = Request::fromGlobals();
        $this->response = Response::fromGlobals();
    }

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
        if ($this->config->debug) {
            $this->timings->startMeasurement('engine');
        }

        $path = $this->pathname();
        $action = "index";

        if ($this->tryServeStatic($path)) {
            return;
        }

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

            if ($this->config->debug) {
                $this->timings->startMeasurement('action');
            }

            $controller->$action();

            if ($this->config->debug) {
                $this->timings->stopMeasurement('action');
            }
        } catch (HttpException $e) {
            $this->setStatus($e->getStatus());
            $this->printError($e->getStatus(), $e->getMessage(), $e);
            return;
        } catch (\Throwable $e) {
            $this->setStatus(500);
            $this->printError(500, "Internal Server Error", $e);
            return;
        }

        if ($this->config->debug) {
            $this->timings->stopMeasurement('engine');
        }
        $this->timings->setRequestMeta($this->request->method(), $this->request->path());
        $this->printTimings();
    }

    private function printTimings(): void
    {
        if ($this->config->debug) {
            $timingsHtml = $this->timings->toHtml();
            echo $timingsHtml;
        }
    }

    private function printError(int $status, string $message, \Throwable $e): void
    {
        echo "{$status} {$message}";
        if ($this->config->debug) {
            echo "<br>";
            echo "<br>";
            echo "Debug mode: <b>" . ($this->config->debug ? "enabled" : "disabled") . "</b>";
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
            $controllerClass = $this->controllerMappings[$route];
            $controller = new $controllerClass($this->config, $this->request, $this->response, $this->timings);

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

    /**
     * Register a static directory to serve files from for a given route prefix.
     *
     * @param string $routePrefix URL prefix (e.g. '/uploads').
     * @param string $directory Filesystem directory to serve from.
     * @return void
     */
    public function registerStaticDir(string $routePrefix, string $directory): void
    {
        $normalizedPrefix = '/' . ltrim($routePrefix, '/');
        $realBase = PathUtils::realpath($directory) ?: $directory;
        if (!is_dir($realBase)) {
            throw new HttpException(500, "Static directory not found: {$directory}");
        }
        $realBase = rtrim(PathUtils::realpath($realBase) ?: $realBase, DIRECTORY_SEPARATOR);
        $this->staticDirectories[] = [
            'prefix' => $normalizedPrefix,
            'base' => $realBase,
        ];
    }

    /**
     * Try to serve a static file for the current path. Returns true if handled.
     *
     * @param string $path
     * @return bool
     */
    private function tryServeStatic(string $path): bool
    {
        foreach ($this->staticDirectories as $staticDir) {
            $prefix = $staticDir['prefix'];
            $matchesPrefix = $path === $prefix || str_starts_with($path, $prefix . '/');
            if (!$matchesPrefix) {
                continue;
            }

            $relative = ltrim(substr($path, strlen($prefix)), '/');
            $candidate = $staticDir['base'] . ($relative !== '' ? DIRECTORY_SEPARATOR . $relative : '');
            $resolved = PathUtils::realpath($candidate);

            if (!$resolved || !str_starts_with($resolved, $staticDir['base'])) {
                $this->notFound();
                return true;
            }

            if (is_dir($resolved)) {
                $this->renderDirectoryListing($staticDir['base'], $prefix, $relative);
                return true;
            }

            if (!is_file($resolved)) {
                $this->notFound();
                return true;
            }

            $this->streamFile($resolved);
            return true;
        }

        return false;
    }

    private function renderDirectoryListing(string $basePath, string $prefix, string $relative): void
    {
        $virtualPrefix = rtrim($prefix, '/');
        $dir = $basePath . ($relative !== '' ? DIRECTORY_SEPARATOR . $relative : '');

        $entries = @scandir($dir) ?: [];
        $items = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $fullPath = $dir . DIRECTORY_SEPARATOR . $entry;
            $isDir = is_dir($fullPath);
            $items[] = [
                'name' => $entry,
                'isDir' => $isDir,
            ];
        }

        header('Content-Type: text/html; charset=utf-8');

        echo "<!doctype html><html><head><meta charset='utf-8'><title>Index of " . htmlspecialchars($virtualPrefix . '/' . $relative) . "</title></head><body>";
        echo "<h1>Index of " . htmlspecialchars($virtualPrefix . '/' . $relative) . "</h1>";
        echo "<ul>";

        if ($relative !== '') {
            $parentRelative = '';
            $parts = explode('/', trim($relative, '/'));
            array_pop($parts);
            if (!empty($parts)) {
                $parentRelative = implode('/', $parts);
            }
            $parentHref = $virtualPrefix . ($parentRelative !== '' ? '/' . $parentRelative : '');
            if ($parentHref === '') {
                $parentHref = '/';
            }
            echo "<li><a href=\"" . htmlspecialchars($parentHref === '' ? '/' : $parentHref) . "\">..</a></li>";
        }

        foreach ($items as $item) {
            $nameEsc = htmlspecialchars($item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = $virtualPrefix . ($relative !== '' ? '/' . $relative : '') . '/' . $item['name'];
            if ($item['isDir']) {
                $href .= '/';
            }
            echo "<li><a href=\"" . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\">" . $nameEsc . ($item['isDir'] ? '/' : '') . "</a></li>";
        }

        echo "</ul></body></html>";
    }

    private function streamFile(string $path): void
    {
        // NOTE: requires enabled fileinfo extension in php.ini
        $mime = mime_content_type($path);
        if ($mime) {
            header('Content-Type: ' . $mime);
        }
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
