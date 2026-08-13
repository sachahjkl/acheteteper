<?php

namespace Acheteteper;

use Acheteteper\DataSourceInterface;
use Acheteteper\Timings;
use Acheteteper\Utils\ViewUtils;

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
        public Response $response,
        public Timings $timings
    ) {}

    /** @var callable|null */
    private $datasourceProvider = null;

    /** @var callable|null */
    private $serviceProvider = null;

    /** @var callable|null */
    private $repositoryProvider = null;

    public ?DataSourceInterface $datasource = null;

    private ?string $layout = null;
    private bool $useLayout = true;
    private bool $csrfVerified = false;

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

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
    public function render(string $view, array $data = []): Response
    {
        if ($this->config->debug()) {
            $this->timings->startMeasurement('render');
        }

        $viewBasename = $this->config->viewDir() . DIRECTORY_SEPARATOR . $view;
        $viewFile = null;
        foreach (Config::$viewExtensions as $extension) {
            $viewPath = $viewBasename . '.' . $extension;
            $resolved = realpath($viewPath);
            if ($resolved !== false && $this->isInViewDir($resolved)) {
                $viewFile = $resolved;
                break;
            }
        }

        if ($viewFile === null) {
            throw new \Exception("View not found: $viewBasename");
        }

        extract($data, EXTR_SKIP);

        ViewUtils::setContext($this->request, $this->response, $this->config);
        $layoutFile = $this->config->viewDir() . DIRECTORY_SEPARATOR . '_layout.phtml';

        if ($this->layout) {
            $layoutFile = $this->config->viewDir() . DIRECTORY_SEPARATOR . $this->layout . '.phtml';
        }
        $resolvedLayout = realpath($layoutFile);
        $layoutFile = $resolvedLayout !== false && $this->isInViewDir($resolvedLayout) ? $resolvedLayout : '';

        ob_start();
        try {
            if ($this->useLayout && file_exists($layoutFile)) {
                ViewUtils::startCaptureViewContent();
                require $viewFile;
                ViewUtils::endCaptureViewContent();
                require $layoutFile;
            } else {
                require $viewFile;
            }
        } finally {
            $this->response->setBody((string) ob_get_clean());
            ViewUtils::clearContext();

            if ($this->config->debug()) {
                $this->timings->stopMeasurement('render');
            }
        }
        return $this->response;
    }

    public function renderPartial(string $view, array $data = []): Response
    {
        $this->useLayout = false;
        return $this->render($view, $data);
    }

    private function isInViewDir(string $path): bool
    {
        $base = rtrim($this->config->viewDir(), DIRECTORY_SEPARATOR);
        return $path === $base || str_starts_with($path, $base . DIRECTORY_SEPARATOR);
    }

    /**
     * Redirect to a URL.
     * 
     * @param string $url Target URL.
     * @return void
     */
    public function redirect(string $url): Response
    {
        return $this->response->redirect($url);
    }

    /**
     * Send a JSON response.
     * 
     * @param array $data Data to encode as JSON.
     * @return void
     */
    public function json(array $data): Response
    {
        return $this->response->json($data);
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
        $values = [];
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
    public function redirectWithFlash(string $url, string $key, mixed $value): Response
    {
        Session::setFlash($key, $value);
        return $this->redirect($url);
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

    public function requireOptions(): void
    {
        if (!$this->request->isOptions()) {
            throw new HttpException(405, 'This action requires OPTIONS method');
        }
    }

    public function requireTrace(): void
    {
        if (!$this->request->isTrace()) {
            throw new HttpException(405, 'This action requires TRACE method');
        }
    }

    public function requireConnect(): void
    {
        if (!$this->request->isConnect()) {
            throw new HttpException(405, 'This action requires CONNECT method');
        }
    }

    public function requireQuery(): void
    {
        if (!$this->request->isQuery()) {
            throw new HttpException(405, 'This action requires QUERY method');
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
        if ($this->csrfVerified) {
            return;
        }
        Csrf::verify($this->request->post('_token') ?? $this->request->header('X-CSRF-Token'));
        $this->csrfVerified = true;
    }

    public function verifyGlobalCsrf(): void
    {
        $this->requireCsrf();
    }

    public function setDatasourceProvider(callable $provider): void
    {
        $this->datasourceProvider = $provider;
    }

    public function setServiceProvider(callable $provider): void
    {
        $this->serviceProvider = $provider;
    }

    public function setRepositoryProvider(callable $provider): void
    {
        $this->repositoryProvider = $provider;
    }

    /**
     * Get a datasource by name.
     */
    public function datasource(string $name = 'default'): DataSourceInterface
    {
        if ($this->datasourceProvider === null) {
            throw new HttpException(500, 'Datasource provider not set');
        }
        return ($this->datasourceProvider)($name);
    }

    /**
     * Resolve a service instance.
     */
    public function getService(string $class): object
    {
        if ($this->serviceProvider === null) {
            throw new HttpException(500, 'Service provider not set');
        }
        return ($this->serviceProvider)($class);
    }

    /**
     * Resolve a repository instance.
     */
    public function getRepository(string $class): object
    {
        if ($this->repositoryProvider === null) {
            throw new HttpException(500, 'Repository provider not set');
        }
        return ($this->repositoryProvider)($class);
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

    public function config(): Config
    {
        return $this->config;
    }
}
