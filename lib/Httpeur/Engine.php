<?php

namespace Httpeur;

use ReflectionClass;
use ReflectionType;

/**
 * Engine class
 * 
 * This class in the core of the application and coordinates the request/response cycle.
 * 
 * @package Lib
 * @author  sachahjkl
 * @version 1.0.0
 */
class Engine
{

    private $controllerMappings = [];

    public function __construct(private Config $config) {}

    public function registerController(string $path, string $class)
    {
        $this->controllerMappings[$path] = $class;
    }

    public function run()
    {
        $path = $this->pathname();


        $action = "index";

        // First, try to find the controller by the path.
        $controller = $this->tryFindController($path);

        if ($controller === null) {
            // If the controller is not found, parse the path to get the controller and action.
            $parsedPath = $this->parsePath($path);
            $action = $parsedPath["actionRoute"];
            $controllerRoute = $parsedPath["controllerRoute"];
            // DebugUtils::dump(["controllerRoute" => $controllerRoute, "actionRoute" => $action, "controllerMappings" => $this->controllerMappings]);
            $controller = $this->tryFindController($controllerRoute);
        }

        // If the controller is still not found, return a 404 error.
        if ($controller === null) {
            $this->notFound();
            return;
        }

        // DebugUtils::dump(["controller" => $controller, "action" => $action]);
        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        $controller->$action();
    }


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

        // If the controller route does not start with a slash, add one.
        if (!str_starts_with($parsedPath["controllerRoute"], "/")) {
            $parsedPath["controllerRoute"] = "/" . $parsedPath["controllerRoute"];
        }

        // DebugUtils::titledDump("Path parsing", ["pathParts" => $pathParts, "parsedPath" => $parsedPath]);

        return $parsedPath;
    }

    private function tryFindController(string $route)
    {
        if (isset($this->controllerMappings[$route])) {
            return new $this->controllerMappings[$route]($this->config);
        } else {
            return null;
        }
    }

    private function pathname()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    private function notFound()
    {
        $this->setStatus(404);
        echo "404 Not Found";
        die();
    }

    private function setStatus(int $status)
    {
        http_response_code($status);
    }
}
