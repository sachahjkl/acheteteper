<?php

namespace Acheteteper\Utils;

use Acheteteper\Config;
use Acheteteper\Csrf;
use Acheteteper\Request;
use Acheteteper\Response;
use Acheteteper\Session;

class ViewUtils
{
    private static ?Request $viewRequest = null;
    private static ?Response $viewResponse = null;
    private static ?Config $viewConfig = null;
    private static ?string $viewContent = null;
    private static ?float $renderDurationSecs = null;

    public static function setContext(Request $request, Response $response, Config $config): void
    {
        self::$viewRequest = $request;
        self::$viewResponse = $response;
        self::$viewConfig = $config;
    }

    public static function clearContext(): void
    {
        self::$viewRequest = null;
        self::$viewResponse = null;
        self::$viewConfig = null;
        self::$renderDurationSecs = null;
    }

    public static function request(): Request
    {
        if (!self::$viewRequest) {
            throw new \RuntimeException('View request not set');
        }
        return self::$viewRequest;
    }

    public static function response(): Response
    {
        if (!self::$viewResponse) {
            throw new \RuntimeException('View response not set');
        }
        return self::$viewResponse;
    }

    public static function config(): Config
    {
        if (!self::$viewConfig) {
            throw new \RuntimeException('View config not set');
        }
        return self::$viewConfig;
    }

    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function e(string $string): string
    {
        return self::escape($string);
    }

    public static function url(string $path = '/'): string
    {
        $path = ltrim($path, '/');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base === '/' || $base === '\\') {
            $base = '';
        }
        return $scheme . '://' . $host . $base . '/' . $path;
    }

    public static function link(string $url, string $text, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return '<a href="' . self::escape($url) . '"' . $attrs . '>' . self::escape($text) . '</a>';
    }

    public static function date(string|int $date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($date)) {
            return date($format, (int) $date);
        }
        return date($format, strtotime($date));
    }

    public static function truncate(string $string, int $length, string $suffix = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length) . $suffix;
    }

    public static function csrfField(): string
    {
        return Csrf::field();
    }

    public static function csrfToken(): string
    {
        return Csrf::token();
    }

    public static function flash(string $key, mixed $default = null): mixed
    {
        return Session::flash($key, $default);
    }

    public static function setviewContent(string $viewContent): void
    {
        self::$viewContent = $viewContent;
    }

    public static function getviewContent(): string
    {
        if (!self::$viewContent) {
            throw new \RuntimeException('View content not set');
        }
        return self::$viewContent;
    }

    public static function clearViewContent(): void
    {
        self::$viewContent = null;
    }

    public static function startCaptureViewContent(): void
    {
        ob_start();
    }
    public static function endCaptureViewContent(): void
    {
        self::$viewContent = ob_get_clean();
    }

    public static function setRenderDurationSecs(float $durationMs): void
    {
        self::$renderDurationSecs = $durationMs;
    }

    public static function renderDurationSecs(): float
    {
        if (self::$renderDurationSecs === null) {
            throw new \RuntimeException('Render duration not set');
        }
        return self::$renderDurationSecs;
    }

    public static function renderDurationSecsOrNull(): ?float
    {
        return self::$renderDurationSecs;
    }
}
