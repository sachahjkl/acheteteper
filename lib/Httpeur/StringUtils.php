<?php

namespace Httpeur;

/**
 * Utility class for string operations.
 * 
 * @package Httpeur
 */
class StringUtils
{
    /**
     * Check if a string is null, empty, or contains only whitespace.
     * 
     * @param string $string String to check.
     * @return bool True if the string is null, empty, or whitespace-only.
     */
    public static function isWhitespaceOrNull(string $string)
    {
        if ($string === null) {
            return true;
        }

        $string = trim($string);
        if ($string === '') {
            return true;
        }
        return false;
    }

    /**
     * Split a path string into parts.
     * 
     * @param string $path Path to split.
     * @param string $separator Separator character (default: '/').
     * @param bool $trim Whether to trim each part (default: true).
     * @param bool $removeEmpty Whether to remove empty parts (default: true).
     * @return array<string> Array of path parts.
     */
    public static function splitPath(string $path, string $separator = '/', bool $trim = true, bool $removeEmpty = true)
    {
        $pathParts = explode($separator, $path);
        if ($trim) {
            $pathParts = array_map('trim', $pathParts);
        }
        if ($removeEmpty) {
            $pathParts = array_filter($pathParts, function ($part) {
                return !StringUtils::isWhitespaceOrNull($part);
            });
        }
        return array_values($pathParts);
    }

    /**
     * Convert a string to camelCase.
     * 
     * @param string $string String to convert.
     * @return string CamelCase string.
     */
    public static function camelCase(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return lcfirst($string);
    }

    /**
     * Convert a string to snake_case.
     * 
     * @param string $string String to convert.
     * @return string Snake_case string.
     */
    public static function snakeCase(string $string): string
    {
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        return strtolower($string);
    }

    /**
     * Convert a string to kebab-case.
     * 
     * @param string $string String to convert.
     * @return string Kebab-case string.
     */
    public static function kebabCase(string $string): string
    {
        $string = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);
        return strtolower($string);
    }
}
