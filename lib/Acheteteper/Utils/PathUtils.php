<?php

namespace Acheteteper\Utils;

class PathUtils
{
    public static function splitPath(string $path)
    {
        return StringUtils::splitPath($path);
    }

    /**
     * Reduce a path to its canonical form.
     * 
     * @param string $path Path to reduce.
     * @return string Reduced path.
     */
    public static function realpath(string $path): string
    {
        return realpath($path);
    }
}
