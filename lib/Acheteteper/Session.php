<?php

namespace Acheteteper;

/**
 * Simple session wrapper.
 * 
 * @package Acheteteper
 */
class Session
{
    /**
     * Start session if not already started.
     * 
     * @return void
     */
    private static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Lax',
            ]);
            ini_set('session.use_strict_mode', '1');
            session_start();
        }
    }

    /**
     * Get a session value.
     * 
     * @param string $key Session key.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value.
     * 
     * @param string $key Session key.
     * @param mixed $value Value to set.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists.
     * 
     * @param string $key Session key.
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value.
     * 
     * @param string $key Session key.
     * @return void
     */
    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data.
     * 
     * @return void
     */
    public static function clear(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    /**
     * Destroy the session.
     * 
     * @return void
     */
    public static function destroy(): void
    {
        self::ensureStarted();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function regenerate(): void
    {
        self::ensureStarted();
        session_regenerate_id(true);
    }

    /**
     * Get a flash message and remove it.
     * 
     * @param string $key Flash message key.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public static function flash(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Set a flash message.
     * 
     * @param string $key Flash message key.
     * @param mixed $value Flash message value.
     * @return void
     */
    public static function setFlash(string $key, mixed $value): void
    {
        self::ensureStarted();
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$key] = $value;
    }
}
