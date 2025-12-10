<?php

namespace Httpeur;

class ControllerBase
{
    public function __construct(private Config $config) {}

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

    public function redirect(string $url)
    {
        header("Location: $url");
        exit();
    }

    public function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function setStatus(int $status)
    {
        http_response_code($status);
    }

    public function notFoud()
    {
        $this->setStatus(404);
        echo "404 Not Found";
        exit();
    }

    public function getFieldValue(string $key)
    {
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }

    public function getFieldsValues(array $keys)
    {
        foreach ($keys as $key) {
            $values[$key] = $this->getFieldValue($key);
        }
        return $values;
    }
}
