<?php

namespace Httpeur;

/**
 * Base class for all controllers.
 * 
 * Provides common functionality for rendering views, handling redirects,
 * JSON responses, and accessing request data.
 * 
 * @package Httpeur
 */
class ControllerBase
{
    /**
     * @param Config $config Framework configuration.
     */
    public function __construct(private Config $config) {}

    /**
     * Render a view template with optional data.
     * 
     * Searches for the view file using supported extensions in order of preference.
     * 
     * @param string $view View name (without extension).
     * @param array $data Data to pass to the view (extracted as variables).
     * @return void
     * @throws \Exception If the view file is not found.
     */
    public function render(string $view, array $data = [])
    {
        $viewBasename = $this->config->viewDir . DIRECTORY_SEPARATOR . $view;
        foreach (Config::$viewExtensions as $extension) {
            $view = $viewBasename . '.' . $extension;
            if (file_exists($view)) {
                extract($data);
                require $view;
                return;
            }
        }

        throw new \Exception("View not found: $viewBasename");
    }

    /**
     * Redirect to a URL.
     * 
     * @param string $url Target URL.
     * @return void
     */
    public function redirect(string $url)
    {
        header("Location: $url");
        exit();
    }

    /**
     * Send a JSON response.
     * 
     * @param array $data Data to encode as JSON.
     * @return void
     */
    public function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Set the HTTP response status code.
     * 
     * @param int $status HTTP status code.
     * @return void
     */
    public function setStatus(int $status)
    {
        http_response_code($status);
    }

    /**
     * Send a 404 Not Found response.
     * 
     * @return void
     */
    public function notFoud()
    {
        $this->setStatus(404);
        echo "404 Not Found";
        exit();
    }

    /**
     * Get a single field value from POST or GET request.
     * 
     * @param string $key Field name.
     * @return mixed Field value or null if not found.
     */
    public function getFieldValue(string $key)
    {
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }

    /**
     * Get multiple field values from POST or GET request.
     * 
     * @param array $keys Array of field names.
     * @return array Associative array of field names and their values.
     */
    public function getFieldsValues(array $keys)
    {
        foreach ($keys as $key) {
            $values[$key] = $this->getFieldValue($key);
        }
        return $values;
    }
}
