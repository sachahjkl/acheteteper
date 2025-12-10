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
}
