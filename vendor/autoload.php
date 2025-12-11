<?php


$debug = false;
function debug($message)
{
    global $debug;
    if ($debug) {
        echo $message;
        echo "<br>";
    }
}

/**
 * Simple autoloader, so we don't need Composer just for this.
 * cf. https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation
 */
function autoload($className)
{
    $className = ltrim($className, '\\');
    $originalFileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $originalFileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $originalFileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    // dossiers spéciaux à chercher
    $dirs = [
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor',
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib',
    ];

    global $baseNamespace;
    debug("baseNamespace: " . $baseNamespace);
    debug("originalFileName: " . $originalFileName);
    if (file_exists($originalFileName)) {
        require $originalFileName;
        return true;
    }

    // $filenames = [$originalFileName, str_replace($baseNamespace . DIRECTORY_SEPARATOR, '', $originalFileName)];
    $filenames = [$originalFileName];

    // echo "filenames: " . implode(', ', $filenames);
    // echo "<br>";

    foreach ($filenames as $filename) {
        foreach ($dirs as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $filename;
            $filePath = realpath($path);
            debug("looking for '$path' => '$filePath' ");

            if (file_exists($filePath)) {
                require $filePath;
                debug("✅ FOUND '$filePath' <br>");
                return true;
            }
        }
    }
    debug("❌ NOT FOUND <br>");
    return false;
}

// Registers the autoloader.
spl_autoload_register('autoload');
