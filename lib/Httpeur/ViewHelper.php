<?php

namespace Httpeur;

/**
 * View helper functions for common operations.
 * 
 * @package Httpeur
 */
class ViewHelper
{
    /**
     * Escape HTML special characters.
     * 
     * @param string $string String to escape.
     * @return string Escaped string.
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a URL from a path.
     * 
     * @param string $path URL path.
     * @return string Full URL.
     */
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

    /**
     * Generate a link HTML element.
     * 
     * @param string $url URL.
     * @param string $text Link text.
     * @param array $attributes Additional HTML attributes.
     * @return string HTML anchor tag.
     */
    public static function link(string $url, string $text, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        return '<a href="' . self::escape($url) . '"' . $attrs . '>' . self::escape($text) . '</a>';
    }

    /**
     * Format a date.
     * 
     * @param string|int $date Date string or timestamp.
     * @param string $format Date format (default: 'Y-m-d H:i:s').
     * @return string Formatted date.
     */
    public static function date($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($date)) {
            return date($format, $date);
        }
        return date($format, strtotime($date));
    }

    /**
     * Truncate a string to a maximum length.
     * 
     * @param string $string String to truncate.
     * @param int $length Maximum length.
     * @param string $suffix Suffix to append if truncated.
     * @return string Truncated string.
     */
    public static function truncate(string $string, int $length, string $suffix = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length) . $suffix;
    }
}
