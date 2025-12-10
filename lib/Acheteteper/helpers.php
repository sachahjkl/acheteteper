<?php

namespace Acheteteper;

/**
 * Global helper functions for use in views.
 * 
 * @package Acheteteper
 */

if (!function_exists('Acheteteper\e')) {
    /**
     * Escape HTML special characters.
     * 
     * @param string $string String to escape.
     * @return string Escaped string.
     */
    function e(string $string): string
    {
        return ViewHelper::escape($string);
    }
}

if (!function_exists('Acheteteper\url')) {
    /**
     * Generate a URL from a path.
     * 
     * @param string $path URL path.
     * @return string Full URL.
     */
    function url(string $path = '/'): string
    {
        return ViewHelper::url($path);
    }
}

if (!function_exists('Acheteteper\csrf_field')) {
    /**
     * Generate CSRF token field HTML.
     * 
     * @return string HTML input field.
     */
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('Acheteteper\csrf_token')) {
    /**
     * Get CSRF token.
     * 
     * @return string CSRF token.
     */
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('Acheteteper\flash')) {
    /**
     * Get and remove a flash message.
     * 
     * @param string $key Flash message key.
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    function flash(string $key, mixed $default = null): mixed
    {
        return Session::flash($key, $default);
    }
}
