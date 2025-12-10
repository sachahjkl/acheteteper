<?php

namespace Acheteteper;

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
            return;
        }
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
            return new $this->controllerMappings[$route]($this->config, $request, $response);
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
