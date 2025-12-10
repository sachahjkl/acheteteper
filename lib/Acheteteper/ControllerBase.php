<?php

namespace Acheteteper;

/**
 * Base class for all controllers.
 * 
 * Provides common functionality for rendering views, handling redirects,
 * JSON responses, and accessing request data.
 * 
 * @package Acheteteper
 */
class ControllerBase
{
    /**
     * @param Config $config Framework configuration.
     * @param Request $request Request instance.
     * @param Response $response Response instance.
     */
    public function __construct(
        private Config $config,
        public Request $request,
        public Response $response
    ) {}

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
        static $helpersLoaded = false;
        if (!$helpersLoaded) {
            require __DIR__ . '/helpers.php';
            $helpersLoaded = true;
        }

        $viewBasename = $this->config->viewDir . DIRECTORY_SEPARATOR . $view;
        $viewFile = null;
        foreach (Config::$viewExtensions as $extension) {
            $viewPath = $viewBasename . '.' . $extension;
            if (file_exists($viewPath)) {
                $viewFile = $viewPath;
                break;
            }
        }

        if ($viewFile === null) {
            throw new \Exception("View not found: $viewBasename");
        }

        extract($data);

        $layoutFile = $this->config->viewDir . DIRECTORY_SEPARATOR . '_layout.phtml';
        if (file_exists($layoutFile)) {
            // Views are responsible for setting $title/$content/$extraStyles.
            require $viewFile;
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    /**
     * Redirect to a URL.
     * 
     * @param string $url Target URL.
     * @return void
     */
    public function redirect(string $url)
    {
        $this->response->redirect($url);
    }

    /**
     * Send a JSON response.
     * 
     * @param array $data Data to encode as JSON.
     * @return void
     */
    public function json(array $data)
    {
        $this->response->json($data);
    }

    /**
     * Set the HTTP response status code.
     * 
     * @param int $status HTTP status code.
     * @return void
     */
    public function setStatus(int $status)
    {
        $this->response->setStatus($status);
    }

    /**
     * Get a single field value from POST or GET request.
     * 
     * @param string $key Field name.
     * @return mixed Field value or null if not found.
     */
    public function getFieldValue(string $key)
    {
        return $this->request->input($key);
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

    /**
     * Get request method (GET, POST, etc.).
     * 
     * @return string
     */
    public function method(): string
    {
        return $this->request->method();
    }

    /**
     * Check if request is POST.
     * 
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->request->isPost();
    }

    /**
     * Check if request is GET.
     * 
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->request->isGet();
    }

    /**
     * Check if request is PUT.
     * 
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->request->isPut();
    }

    /**
     * Check if request is DELETE.
     * 
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->request->isDelete();
    }

    /**
     * Check if request is PATCH.
     * 
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->request->isPatch();
    }

    /**
     * Check if request is HEAD.
     * 
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->request->isHead();
    }

    /**
     * Redirect with a flash message.
     * 
     * @param string $url Target URL.
     * @param string $key Flash message key.
     * @param mixed $value Flash message value.
     * @return void
     */
    public function redirectWithFlash(string $url, string $key, mixed $value): void
    {
        Session::setFlash($key, $value);
        $this->redirect($url);
    }

    /**
     * Validate request data.
     * 
     * @param array $data Data to validate (defaults to POST data).
     * @return Validator
     */
    public function validate(?array $data = null): Validator
    {
        if ($data === null) {
            $data = $this->request->allPost();
        }
        return new Validator($data);
    }

    /**
     * Require POST method, throw exception if not.
     * 
     * @return void
     * @throws \Exception If request is not POST.
     */
    public function requirePost(): void
    {
        if (!$this->request->isPost()) {
            throw new HttpException(405, 'This action requires POST method');
        }
    }

    /**
     * Require GET method, throw exception if not.
     * 
     * @return void
     * @throws HttpException If request is not GET.
     */
    public function requireGet(): void
    {
        if (!$this->request->isGet()) {
            throw new HttpException(405, 'This action requires GET method');
        }
    }

    /**
     * Require PUT method, throw exception if not.
     * 
     * @return void
     * @throws HttpException If request is not PUT.
     */
    public function requirePut(): void
    {
        if (!$this->request->isPut()) {
            throw new HttpException(405, 'This action requires PUT method');
        }
    }

    /**
     * Require DELETE method, throw exception if not.
     * 
     * @return void
     * @throws HttpException If request is not DELETE.
     */
    public function requireDelete(): void
    {
        if (!$this->request->isDelete()) {
            throw new HttpException(405, 'This action requires DELETE method');
        }
    }

    /**
     * Require PATCH method, throw exception if not.
     * 
     * @return void
     * @throws HttpException If request is not PATCH.
     */
    public function requirePatch(): void
    {
        if (!$this->request->isPatch()) {
            throw new HttpException(405, 'This action requires PATCH method');
        }
    }

    /**
     * Require HEAD method, throw exception if not.
     * 
     * @return void
     * @throws HttpException If request is not HEAD.
     */
    public function requireHead(): void
    {
        if (!$this->request->isHead()) {
            throw new HttpException(405, 'This action requires HEAD method');
        }
    }

    /**
     * Require valid CSRF token, throw exception if not.
     * 
     * @return void
     * @throws HttpException If CSRF token is invalid.
     */
    public function requireCsrf(): void
    {
        Csrf::verify();
    }

    /**
     * Throw an HTTP exception with status and message.
     * 
     * @param int $status HTTP status code.
     * @param string $message Error message.
     * @return never
     * @throws HttpException
     */
    public function fail(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }
}
