<?php

namespace Httpeur;

class StringUtils
{
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
