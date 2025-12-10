<?php

/**
 * Simple autoloader, so we don't need Composer just for this.
 * 
 * 
 */
function autoload($className)
{
    // cf. https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    // dossiers spéciaux à chercher
    $dirs = [
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor',
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib',
    ];

    if (file_exists($fileName)) {
        require $fileName;
        return true;
    }

    foreach ($dirs as $dir) {
        if (file_exists($dir . DIRECTORY_SEPARATOR . $fileName)) {
            require $dir . DIRECTORY_SEPARATOR . $fileName;
            return true;
        }
    }
    return false;
}

// Registers the autoloader.
spl_autoload_register('autoload');
